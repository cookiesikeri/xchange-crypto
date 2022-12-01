<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AirtimeVendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $airtimePurchase;
    public function __construct(\App\Models\AirtimeTransaction $airtimePurchase)
    {
        $this->airtimePurchase = $airtimePurchase;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('hello@taheerexchange.com', 'TaheerXchange Nigeria')->subject('TaheerXchange Airtime Purchase')->view('mails.airtime_notification');
    }
}
