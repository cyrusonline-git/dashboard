<?php

namespace App\Services;

/** Wie mag wat zien (zie plan: iedereen eigen gegevens; rol bepaalt het overzicht). */
class Rollen
{
    public static function magAvi(?string $rol = null): bool
    {
        return in_array($rol ?? actieve_rol(), ['binnendienst', 'area_manager', 'bu_manager', 'admin'], true);
    }

    public static function magFactuurrun(?string $rol = null): bool
    {
        return in_array($rol ?? actieve_rol(), ['binnendienst', 'area_manager', 'bu_manager', 'admin', 'servicedesk'], true);
    }

    public static function magTeam(?string $rol = null): bool
    {
        return in_array($rol ?? actieve_rol(), config('core.team_rollen', []), true);
    }

    public static function magNotitie(?string $rol = null): bool
    {
        return in_array($rol ?? actieve_rol(), ['binnendienst', 'expeditie', 'area_manager', 'bu_manager', 'admin', 'servicedesk'], true);
    }

    /** employee_id van de ingelogde gebruiker volgens CORE (/api/me). */
    public static function eigenEmployeeId(): ?int
    {
        $id = core_gebruiker()['employee_id'] ?? null;

        return $id ? (int) $id : null;
    }
}
