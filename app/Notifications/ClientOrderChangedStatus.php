<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Contact;
use PDF;
use Storage;
use App;

class ClientOrderChangedStatus extends Notification
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
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
        return (new MailMessage)->markdown('mail.order.clientOrderChangedStatusMessage', ['order' => $this->order])
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/order.changedStatusOrderSubject', ['orderNumber' => $this->order->id]))
                                ->greeting(trans('notification/order.greeting').' '.$this->order->client_name)
                                ->line(trans('notification/order.changedStatusOrderLine1', ['orderNumber' => $this->order->id]))
                                ->line(trans('notification/order.changedStatusOrderLine3'))
                                ->action(trans('notification/order.action'), url(route('client-account')))                                
                                ->salutation(trans('notification/order.salutation'));
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
