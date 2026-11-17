<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/** Aanvraag bij een collega-depot (voorraadcheck). */
class Aanvraag extends Model
{
    protected $table = 'voorraad_aanvragen';

    protected $guarded = [];

    protected $casts = ['regels' => 'array', 'contracten' => 'array', 'verhuurdatum' => 'date', 'reactie_voor' => 'date', 'gereageerd_op' => 'datetime'];

    public const STATUSSEN = [
        'verzonden' => ['Wacht op reactie', 'warning'],
        'toegezegd' => ['Toegezegd', 'success'],
        'deels' => ['Deels toegezegd', 'info'],
        'afgewezen' => ['Afgewezen', 'danger'],
        'ontvangen' => ['Ontvangen', 'secondary'],
        'geannuleerd' => ['Geannuleerd', 'light'],
        'mislukt' => ['Mail mislukt', 'dark'],
    ];

    /** Statussen waarin de aantallen meetellen als "geregeld" resp. "wacht op reactie". */
    public const GEREGELD = ['toegezegd', 'deels', 'ontvangen'];

    public const WACHT = ['verzonden'];

    public function label(): string
    {
        return self::STATUSSEN[$this->status][0] ?? $this->status;
    }

    public function kleur(): string
    {
        return self::STATUSSEN[$this->status][1] ?? 'secondary';
    }

    /** Toegezegde aantallen per subgroep (toegezegd/ontvangen = alles, deels = wat het depot invulde, anders 0). */
    public function toegezegdPerSubgroep(): array
    {
        $uit = [];
        foreach ((array) $this->regels as $r) {
            $n = match ($this->status) {
                'toegezegd', 'ontvangen' => (int) $r['aantal'],
                'deels' => (int) ($r['toegezegd'] ?? 0),
                default => 0,
            };
            $uit[(string) $r['subgroep']] = ($uit[(string) $r['subgroep']] ?? 0) + $n;
        }

        return $uit;
    }

    public function aantalPerSubgroep(): array
    {
        $uit = [];
        foreach ((array) $this->regels as $r) {
            $uit[(string) $r['subgroep']] = ($uit[(string) $r['subgroep']] ?? 0) + (int) $r['aantal'];
        }

        return $uit;
    }

    /** Beveiligde link voor het collega-depot (geen login nodig), 45 dagen geldig. */
    public function reactieUrl(?string $keuze = null): string
    {
        return URL::temporarySignedRoute('aanvraag.reactie', now()->addDays(45), array_filter(['token' => $this->token, 'keuze' => $keuze]));
    }
}
