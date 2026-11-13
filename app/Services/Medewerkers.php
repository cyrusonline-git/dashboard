<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Medewerkerkaarten uit CORE, 10 minuten gecached (gedeeld voor alle bezoekers),
 * plus de teamafbakening voor managers.
 */
class Medewerkers
{
    public function __construct(private CoreSso $sso)
    {
    }

    /** Alle actieve medewerkers (array per medewerker, zoals CORE ze geeft), op naam. */
    public function alle(bool $vernieuw = false): Collection
    {
        if ($vernieuw) {
            Cache::forget('core.employees');
        }
        $lijst = Cache::remember('core.employees', 600, function () {
            $rijen = $this->sso->employees();
            if ($rijen === null) {
                Cache::forget('core.employees'); // geen lege lijst vastzetten bij een storing

                return [];
            }

            return $rijen;
        });

        return collect($lijst)->map(function ($m) {
            $m['email'] = isset($m['email']) && $m['email'] !== '' ? mb_strtolower(trim($m['email'])) : null;
            $m['employee_number'] = isset($m['employee_number']) ? (string) $m['employee_number'] : null;

            return $m;
        })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function opEmail(?string $email): ?array
    {
        if (! $email) {
            return null;
        }
        $email = mb_strtolower(trim($email));

        return $this->alle()->first(fn ($m) => $m['email'] === $email);
    }

    public function opId(?int $id): ?array
    {
        return $id ? $this->alle()->first(fn ($m) => (int) ($m['id'] ?? 0) === $id) : null;
    }

    public function opNummer(?string $nr): ?array
    {
        return $nr ? $this->alle()->first(fn ($m) => $m['employee_number'] === (string) $nr) : null;
    }

    /**
     * Het team van de ingelogde manager: medewerkers in zijn toegestane depots/areas
     * (uit CORE). BU-managers, beheerders en super-admins zien iedereen.
     */
    public function team(): Collection
    {
        $alle = $this->alle();
        $rol = actieve_rol();
        $gebruiker = core_gebruiker() ?? [];
        if (! Rollen::magTeam()) {
            return collect();
        }
        if (Rollen::heeft(['admin', 'bu_manager']) || ! empty($gebruiker['is_super_admin'])) {
            return $alle;
        }
        $depots = array_map('mb_strtolower', (array) session('toegestane_depots', []));
        $areas = array_map('mb_strtolower', (array) session('toegestane_areas', []));
        if (! $depots && ! $areas) {
            return collect();
        }

        return $alle->filter(fn ($m) => in_array(mb_strtolower((string) ($m['depot'] ?? '')), $depots, true)
            || in_array(mb_strtolower((string) ($m['area'] ?? '')), $areas, true))->values();
    }
}
