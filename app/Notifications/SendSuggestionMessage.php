<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Suggestion;

class SendSuggestionMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $message;

    public function __construct(Suggestion $message)
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
        return (new MailMessage)->markdown('mail.admin.suggestionMessage')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/suggestion.subject'))
                                ->greeting(trans('notification/suggestion.greeting'))
                                ->line(trans('notification/suggestion.line1'))
                                ->line(trans('notification/suggestion.type').' '.trans('notification/suggestion.'.$this->message->type))
                                ->line(trans('notification/suggestion.name').' '.$this->message->name)
                                ->line(trans('notification/suggestion.email').' '.$this->message->email)
                                ->line(trans('notification/suggestion.phone').' '.$this->message->phone)
                                ->line(trans('notification/suggestion.contentForm').' '.$this->message->content)
                                ->action(trans('notification/suggestion.action'), url(config('app.url').'admin-suggestions-messages'))
                                ->salutation(trans('notification/suggestion.salutation'));
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
