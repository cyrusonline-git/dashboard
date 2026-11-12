<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $fillable = ['wie', 'actie', 'onderwerp', 'details', 'ip'];
    protected $casts = ['details' => 'array'];

    /** Snel loggen: audit('bron.sync', 'projectschade', [...]) */
    public static function schrijf(string $actie, ?string $onderwerp = null, array $details = []): void
    {
        try {
            static::create([
                'wie' => core_gebruiker()['name'] ?? (app()->runningInConsole() ? 'scheduler' : 'api'),
                'actie' => $actie, 'onderwerp' => $onderwerp, 'details' => $details ?: null,
                'ip' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
