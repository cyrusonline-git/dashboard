<?php

namespace App\Mail;

use App\Models\Aanvraag;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Terugkoppeling aan de aanvrager zodra het collega-depot heeft gereageerd. */
class AanvraagReactieMail extends Mailable
{
    public function __construct(public Aanvraag $a, public ?string $opmerking = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->a->depot_naam ?: 'Depot '.$this->a->depot).': '.mb_strtolower($this->a->label()).' — aanvraag '.$this->a->nummer);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.reactie');
    }
}
