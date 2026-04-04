<?php

namespace App\Domain\Ticketing\Mails;

use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReadyForPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваше устройство готово к выдаче!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.ready-for-pickup',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
