<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BtcPurchaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected $user,
        public $value,
        public $btcValue
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Compra de Bitcoin",
        );
    }

    public function content(): Content
    {
        $view = "emails.purchase-bitcoin";

        return new Content(
            view: $view,
            with: [
                'user' => $this->user,
                'value' => $this->value,
                'btcValue' => $this->btcValue
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
