<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ReturnedProduct;

class SendReturnProductMessage extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
   public function __construct(ReturnedProduct $message)
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
        return (new MailMessage)->markdown('mail.client.returnProductMessage')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/returnProduct.subject'))
                                ->greeting(trans('notification/returnProduct.greeting'))
                                ->line(trans('notification/returnProduct.line1'))
                                ->line(trans('notification/returnProduct.name').' '.$this->message->name)
                                ->line(trans('notification/returnProduct.email').' '.$this->message->email)
                                ->line(trans('notification/returnProduct.phone').' '.$this->message->phone)
                                ->line(trans('notification/returnProduct.orderNumber').' '.$this->message->order_number)
                                ->line(trans('notification/returnProduct.productCode').' '.$this->message->product_codes)
                                ->line(trans('notification/returnProduct.returnBack').' '.trans('front/common.'.$this->message->return_back))
                                ->action(trans('notification/returnProduct.action'), url(config('app.url').'admin-returned-products'))
                                ->salutation(trans('notification/returnProduct.salutation'));
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
