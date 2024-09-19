<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationTransactionDepositMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected $user,
        public $value
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmação de Valor Depositado",
        );
    }

    public function content(): Content
    {
        $view = "emails.confirm-transaction-deposit";

        return new Content(
            view: $view,
            with: [
                'user' => $this->user,
                'value' => $this->value
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
