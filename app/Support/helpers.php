<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Instelling lezen (met standaardwaarde). */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('rol_naam')) {
    function rol_naam(?string $slug): string
    {
        return config('core.rollen')[$slug] ?? ucfirst((string) $slug);
    }
}

if (! function_exists('actieve_rol')) {
    function actieve_rol(): ?string
    {
        return session('actieve_rol');
    }
}

if (! function_exists('core_gebruiker')) {
    /** Profiel van de ingelogde CORE-gebruiker (array) of null. */
    function core_gebruiker(): ?array
    {
        $u = session('core_user');

        return is_array($u) ? $u : null;
    }
}

if (! function_exists('eigen_email')) {
    function eigen_email(): ?string
    {
        $e = core_gebruiker()['email'] ?? null;

        return $e ? mb_strtolower($e) : null;
    }
}

if (! function_exists('eigen_personeelsnummer')) {
    function eigen_personeelsnummer(): ?string
    {
        $nr = session('eigen_personeelsnummer');

        return $nr ? (string) $nr : null;
    }
}

if (! function_exists('audit')) {
    function audit(string $actie, ?string $onderwerp = null, array $details = []): void
    {
        \App\Models\AuditLog::schrijf($actie, $onderwerp, $details);
    }
}

if (! function_exists('euro')) {
    function euro(float|int|string|null $bedrag): string
    {
        return '€ '.number_format((float) $bedrag, 2, ',', '.');
    }
}

if (! function_exists('dmy')) {
    function dmy(mixed $datum): string
    {
        if (! $datum) {
            return '—';
        }

        return ($datum instanceof \DateTimeInterface ? \Carbon\Carbon::instance($datum) : \Carbon\Carbon::parse($datum))->format('d-m-Y');
    }
}

if (! function_exists('relatief')) {
    /** "vandaag", "morgen", "over 3 dagen", "2 dagen te laat" */
    function relatief(mixed $datum): string
    {
        if (! $datum) {
            return '';
        }
        $d = \Carbon\Carbon::parse($datum)->startOfDay();
        $dagen = (int) now()->startOfDay()->diffInDays($d, false);

        return match (true) {
            $dagen === 0 => 'vandaag',
            $dagen === 1 => 'morgen',
            $dagen === -1 => 'gisteren',
            $dagen > 1 => "over $dagen dagen",
            default => abs($dagen).' dagen te laat',
        };
    }
}
