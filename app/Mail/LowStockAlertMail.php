<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public array $data,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MaterialTrack - Alert Stok Menipis',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.low-stock-alert',
            with: [
                'data' => $this->data,
            ],
        );
    }
}
