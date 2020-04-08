<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Client;

class AdminClientRegisterMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $client;

    public function __construct(Client $client)
    {        
        $this->client = $client;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.admin.clientRegisterMessage')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/clientRegister.adminSubject'))
                                ->greeting(trans('notification/clientRegister.adminGreeting'))
                                ->line(trans('notification/clientRegister.adminLine1'))
                                ->line(trans('notification/clientRegister.adminEmail').' '.$this->client->email)
                                ->line(trans('notification/clientRegister.adminPhone').' '.$this->client->phone)
                                ->action(trans('notification/clientRegister.adminAction'), url(config('app.url').'admin-clients'))
                                ->salutation(trans('notification/clientRegister.adminSalutation'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
