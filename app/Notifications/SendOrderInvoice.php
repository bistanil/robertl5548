<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\OrderInvoice;
use App\Models\Order;

class SendOrderInvoice extends Notification
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
        return (new MailMessage)->markdown('mail.order.invoiceSent')
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/invoice.subject'))
                                ->greeting(trans('notification/invoice.greeting').' '.$this->invoice->client_name)
                                ->line(trans('notification/invoice.line1'))
                                //->line(trans('notification/invoice.title').' '.$this->invoice->title)
                                ->attach(base_path('public_html/public') . '/orders/invoices/' . $this->invoice->docs)
                                ->action(trans('notification/invoice.action'), url(route('client-account')))
                                ->salutation(trans('notification/invoice.salutation'));
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
