<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\OfferRequest;

class SendRequestOfferMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $message;
    protected $type;

    public function __construct(OfferRequest $message, $type)
    {
        $this->message = $message;
        $this->type = $type;
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
        $email = new MailMessage();
        $email->markdown('mail.admin.requestOfferMessage')
              ->from(config('mail.defaultEmail'), config('mail.from.name'))
              ->subject(trans('notification/requestOffer.subject'))
              ->greeting(trans('notification/requestOffer.greeting'))
              ->line(trans('notification/requestOffer.line1'))
              ->line(trans('notification/requestOffer.name').' '.$this->message->name)
              ->line(trans('notification/requestOffer.email').' '.$this->message->email)
              ->line(trans('notification/requestOffer.phone').' '.$this->message->phone)
              ->line(trans('notification/requestOffer.vin').' '.$this->message->vin);        
        if ($this->type != null) $email->line(trans('notification/requestOffer.car').' '.$this->type->model->modelsGroup->car->title.' '.$this->type->model->title.' '.$this->type->title.' '.$this->type->fuel.' '.$this->type->kw);         
        return $email->line(trans('notification/requestOffer.contentForm').' '.$this->message->content)
                     ->action(trans('notification/requestOffer.action'), url(config('app.url').'admin-offer-requests'))
                     ->salutation(trans('notification/requestOffer.salutation'));                                
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
