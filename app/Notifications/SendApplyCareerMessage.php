<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\CareerApply;

class SendApplyCareerMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $message;

    public function __construct(CareerApply $message)
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
        return (new MailMessage)->markdown('mail.admin.applyCareerMessage')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/career.subject'))
                                ->greeting(trans('notification/career.greeting'))
                                ->line(trans('notification/career.line1'))
                                ->line(trans('notification/career.name').' '.$this->message->name)
                                ->line(trans('notification/career.email').' '.$this->message->email)
                                ->line(trans('notification/career.phone').' '.$this->message->phone)
                                ->line(trans('notification/career.career').' '.$this->message->career->title)
                                ->salutation(trans('notification/career.salutation'))
                                ->attach(base_path('public_html/public') . '/careers/' . $this->message->docs);
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
