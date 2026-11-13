<?php

namespace App\Services;

/**
 * Wie mag wat zien. Toegang kijkt naar ALLE dashboardrollen van de gebruiker (volgens CORE),
 * de ACTIEVE rol bepaalt alleen wat standaard wordt getoond (bv. "mijn contracten" voor binnendienst).
 */
class Rollen
{
    /** Heeft de gebruiker één van deze rollen (ongeacht welke actief is)? */
    public static function heeft(array $rollen): bool
    {
        $mijn = (array) session('rollen', []);
        if ($a = actieve_rol()) {
            $mijn[] = $a;
        }

        return (bool) array_intersect($rollen, $mijn);
    }

    public static function magAvi(?string $rol = null): bool
    {
        return $rol ? in_array($rol, ['binnendienst', 'area_manager', 'bu_manager', 'admin'], true) || self::heeft(['area_manager', 'bu_manager', 'admin']) : self::heeft(['binnendienst', 'area_manager', 'bu_manager', 'admin']);
    }

    public static function magFactuurrun(?string $rol = null): bool
    {
        return self::heeft(['binnendienst', 'area_manager', 'bu_manager', 'admin', 'servicedesk']);
    }

    public static function magTeam(?string $rol = null): bool
    {
        return self::heeft(config('core.team_rollen', []));
    }

    public static function magNotitie(?string $rol = null): bool
    {
        return self::heeft(['binnendienst', 'expeditie', 'area_manager', 'bu_manager', 'admin', 'servicedesk']);
    }

    /** employee_id van de ingelogde gebruiker volgens CORE (/api/me). */
    public static function eigenEmployeeId(): ?int
    {
        $id = core_gebruiker()['employee_id'] ?? null;

        return $id ? (int) $id : null;
    }
}
