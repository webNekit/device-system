<?php

namespace App\Domain\Ticketing\Mails;

use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public MagicLink $link
    ) {}

    public function build()
    {
        return $this->subject('Согласование ремонта: '.$this->ticket->device_brand)
            ->view('emails.magic-link');
    }
}
