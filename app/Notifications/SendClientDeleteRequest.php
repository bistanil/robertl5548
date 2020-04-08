<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ClientDeleteRequest;

class SendClientDeleteRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $message;

    public function __construct(ClientDeleteRequest $message)
    {
        $this->message = $message;
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
        return (new MailMessage)->markdown('mail.admin.clientDeleteRequest')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/clientDeleteRequest.subject'))
                                ->greeting(trans('notification/clientDeleteRequest.greeting'))
                                ->line(trans('notification/clientDeleteRequest.line1'))
                                ->line(trans('notification/clientDeleteRequest.name').' '.$this->message->name)
                                ->line(trans('notification/clientDeleteRequest.email').' '.$this->message->email)
                                ->line(trans('notification/clientDeleteRequest.phone').' '.$this->message->phone)
                                ->line(trans('notification/clientDeleteRequest.deleteInfo').' '.trans('front/forms.'.$this->message->account_action))
                                ->line(trans('notification/clientDeleteRequest.contentForm').' '.$this->message->content)
                                ->action(trans('notification/clientDeleteRequest.action'), url(config('app.url').'/admin-client-delete-request'))
                                ->salutation(trans('notification/clientDeleteRequest.salutation'));
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
            
        ];
    }
}