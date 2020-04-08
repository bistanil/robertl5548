<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Logo;
use PDF;
use Storage;
use App;

class AdminOrderReceived extends Notification
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
        return (new MailMessage)->markdown('mail.order.adminOrderReceivedMessage', ['order' => $this->order])
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/order.adminOrderSubject', ['orderNumber' => $this->order->id]))
                                ->greeting(trans('notification/order.adminGreeting'))
                                ->line(trans('notification/order.adminLine1', ['orderNumber' => $this->order->id]))                                
                                ->salutation(trans('notification/order.adminSalutation'))
                                ->attach($this->getProforma());
    }

    private function getProforma()
    {
        $order = $this->order;
        $contactInfo = Contact::whereLanguage(App::getLocale())->get()->first();
        $logo = Logo::whereType('proforma')->whereLanguage(locale())->get()->first();
        $headerHtml = view()->make('admin.partials.orders.proforma.header', compact('logo','order', 'contactInfo'))->render();
        $footerHtml = view()->make('admin.partials.orders.proforma.footer', compact('logo','order', 'contactInfo'))->render();
        $pdf = PDF::loadView('admin.layouts.proforma', compact('logo','order', 'contactInfo'))
                   ->setPaper('a4')
                   ->setOption('margin-top', '30mm')
                   ->setOption('margin-bottom', '30mm')
                   ->setOption('header-html', $headerHtml)
                   ->setOption('footer-html', $footerHtml);        
        Storage::disk('proforma')->put(trans('admin/orders.proforma').'-'.$order->id.'.pdf', $pdf->output());        
        return realpath("storage/tmp/".trans('admin/orders.proforma')."-".$order->id.".pdf"); 
    }

    private function getWarrantyPDF()
    {
        $contact = Contact::whereLanguage(App::getLocale())->get()->first();
        $page = Page::whereMenu('warranty')->whereLanguage(App::getLocale())->whereActive('active')->get()->first();
        $pdf = PDF::loadView('admin.partials.orders.warrantyPDF', compact('page', 'contact'));
        Storage::disk('proforma')->put('garantie'.'.pdf', $pdf->output());
        return realpath("storage/tmp/garantie.pdf");    
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
