<?php

namespace App\Mail;

use App\Models\Aanvraag;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Aanvraag aan een collega-depot, met knoppen Akkoord / Deels / Nee (ondertekende links). */
class AanvraagMail extends Mailable
{
    public function __construct(public Aanvraag $a) {}

    public function envelope(): Envelope
    {
        $namens = setting('voorraad_mail_van_naam', 'Boels Industrial').' namens '.($this->a->eigen_depot_naam ?: 'depot '.$this->a->eigen_depot);

        return new Envelope(
            from: new Address((string) config('mail.from.address'), $namens),
            replyTo: array_values(array_filter([$this->a->aanvrager_email ? new Address($this->a->aanvrager_email, (string) $this->a->aanvrager_naam) : null])),
            subject: 'Materieelaanvraag '.($this->a->eigen_depot_naam ?: $this->a->eigen_depot).' → '.($this->a->depot_naam ?: $this->a->depot).': '.$this->a->aantal.' st. voor '.($this->a->soort === 'project' ? 'project ' : 'contract ').$this->a->nummer,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.aanvraag');
    }
}
