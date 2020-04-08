<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Warranty;
use App\Models\Order;

class AdminSendWarranty extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $warranty;
    public function __construct(Warranty $warranty)
    {
        $this->warranty = $warranty;
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
        return (new MailMessage)->markdown('mail.order.adminWarrantySent')
                                 ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/warranty.adminSubject'))
                                ->greeting(trans('notification/warranty.adminGreeting'))
                                ->line(trans('notification/warranty.adminLine1').' '.$this->warranty->client_name)
                                //->line(trans('notification/warranty.adminTitle').' '.$this->warranty->title)
                                ->attach(base_path('public_html/public') . '/orders/warranties/' . $this->warranty->docs)
                                ->action(trans('notification/warranty.adminAction'), url(route('admin-orders')))
                                ->salutation(trans('notification/warranty.adminSalutation'));
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
