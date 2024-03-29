<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreditEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $name;
    public $amount;
    public $transaction;
    public $total;

    public function __construct($name,$amount,$transaction,$total)
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->transaction = $transaction;
        $this->total = $total;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('TSN Transaction Alert ['.number_format($this->amount).']')
            ->markdown('emails.credit_emails');
    }
}
