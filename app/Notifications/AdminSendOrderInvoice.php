<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\OrderInvoice;
use App\Models\Order;


class AdminSendOrderInvoice extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $invoice;
    public function __construct(OrderInvoice $invoice)
    {
        $this->invoice = $invoice;
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
        return (new MailMessage)->markdown('mail.order.adminInvoiceSent')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/invoice.adminSubject'))
                                ->greeting(trans('notification/invoice.adminGreeting'))
                                ->line(trans('notification/invoice.adminLine1').' '.$this->invoice->client_name)
                                //->line(trans('notification/invoice.adminSitle').' '.$this->invoice->title)
                                ->attach(base_path('public_html/public') . '/orders/invoices/' . $this->invoice->docs)
                                ->action(trans('notification/invoice.adminAction'), url(route('admin-orders')))
                                ->salutation(trans('notification/invoice.adminSalutation'));
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
