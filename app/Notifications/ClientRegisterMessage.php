<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Client;

class ClientRegisterMessage extends Notification
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
        return (new MailMessage)->markdown('mail.client.registerMessage')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/clientRegister.subject'))
                                ->greeting(trans('notification/clientRegister.greeting').' '.$this->client->name)
                                ->line(trans('notification/clientRegister.line1'))
                                ->line(trans('notification/clientRegister.email').' '.$this->client->email)
                                ->line(trans('notification/clientRegister.phone').' '.$this->client->phone)
                                ->line(trans('notification/clientRegister.password'))
                                ->action(trans('notification/clientRegister.action'), url(route('client-account')))
                                ->salutation(trans('notification/clientRegister.salutation'));
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
