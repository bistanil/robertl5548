<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ContactMessage;

class SendContactMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $message;

    public function __construct(ContactMessage $message)
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
        return (new MailMessage)->markdown('mail.admin.contactMessage')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/contact.subject'))
                                ->greeting(trans('notification/contact.greeting'))
                                ->line(trans('notification/contact.line1'))
                                ->line(trans('notification/contact.name').' '.$this->message->name)
                                ->line(trans('notification/contact.email').' '.$this->message->email)
                                ->line(trans('notification/contact.phone').' '.$this->message->phone)
                                ->line(trans('notification/contact.subjectForm').' '.$this->message->subject)
                                ->line(trans('notification/contact.contentForm').' '.$this->message->content)
                                ->action(trans('notification/contact.action'), url(config('app.url').'admin-contact-messages'))
                                ->salutation(trans('notification/contact.salutation'));
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