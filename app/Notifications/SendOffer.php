<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Contact;
use App\Models\Page;
use PDF;
use Storage;
use App;

class SendOffer extends Notification
{
    use Queueable;

    public $offer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($offer)
    {
        $this->offer = $offer;
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
        return (new MailMessage)->markdown('mail.client.sendOffer', ['offer' => $this->offer])
                                ->from(config('mail.defaultEmail'), config('mail.from.name'))
                                ->subject(trans('notification/offers.subject', ['offerNumber' => $this->offer->title]))
                                ->greeting(trans('notification/offers.greeting').' '.$this->offer->name)
                                ->line(trans('notification/offers.line1', ['offerNumber' => $this->offer->title]))                                
                                ->salutation(trans('notification/offers.salutation'))
                                ->attach($this->getOffer());
    }

    private function getOffer()
    {
        $contact = Contact::whereLanguage(App::getLocale())->get()->first();
        $offer = $this->offer;
        $pdf = PDF::loadView('admin.partials.offers.offerPdf', compact('offer', 'contact'));
        Storage::disk('offer')->put(str_slug($offer->title, "-").'.pdf', $pdf->output());
        return realpath("storage/tmp/".str_slug($offer->title, "-").".pdf"); 
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
