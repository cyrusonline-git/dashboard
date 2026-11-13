<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cookie-relay naar Boels CORE.
 *
 * De browser stuurt zijn CORE-sessiecookie (domein .sorai.nl) ook naar
 * deze app mee; wij sturen die cookie door naar de CORE-API en krijgen zo
 * de ingelogde gebruiker, zijn rollen in deze app, de medewerkerkaarten en
 * de notificatie-tellers (badges) van alle apps. Antwoorden worden een paar
 * minuten bewaard, zodat CORE niet bij elke klik wordt belast.
 */
class CoreSso
{
    public function __construct(private Request $request)
    {
    }

    /** Ingelogde CORE-gebruiker (of null). */
    public function user(bool $force = false): ?array
    {
        if ($this->fakeUser()) {
            $this->request->session()->put('core_user', $this->fakeUser());

            return $this->fakeUser();
        }
        $user = $this->relay('/api/me', 'core_user', $force);

        return (is_array($user) && ! empty($user['id'])) ? $user : null;
    }

    /** Rollen/permissies van de gebruiker binnen déze app volgens CORE. */
    public function access(bool $force = false): ?array
    {
        if ($this->fakeUser()) {
            return [
                'is_super_admin' => true,
                'roles' => collect(config('core.rollen'))->keys()->map(fn ($s) => ['slug' => $s, 'scope' => 'app'])->all(),
            ];
        }

        return $this->relay('/api/access/'.config('core.slug'), 'core_access', $force);
    }

    /**
     * Willekeurig CORE-API-endpoint (GET) namens de ingelogde gebruiker, met korte cache
     * per gebruiker. Geeft het JSON-antwoord als array, of null bij storing/geen toegang.
     */
    public function api(string $endpoint, array $query = [], int $ttl = 60): ?array
    {
        $cookie = $this->coreCookies();
        if ($cookie === '' && ! $this->fakeUser()) {
            return null;
        }
        $url = $endpoint.($query ? (str_contains($endpoint, '?') ? '&' : '?').http_build_query(array_filter($query, fn ($v) => $v !== null && $v !== '')) : '');
        $gid = (int) (core_gebruiker()['id'] ?? 0);
        // Stand van de uploads in de sleutel: een nieuwe upload in CORE ververst alles binnen 30 s
        $stand = str_starts_with($endpoint, '/api/sap/uploads') ? '-' : Cache::remember('core_stand:'.$gid, 30, function () use ($cookie) {
            foreach ([config('core.app_url'), config('core.url')] as $referer) {
                $u = $this->callRaw('/api/sap/uploads', $cookie, $referer);
                if ($u !== null) {
                    return md5(json_encode(array_map(fn ($x) => $x['id'] ?? null, $u)));
                }
            }

            return 'x';
        });
        $sleutel = 'core_api:'.$gid.':'.$stand.':'.(int) Cache::get('core_api_gen:'.$gid, 0).':'.md5($url);
        $haal = function () use ($url, $cookie) {
            foreach ([config('core.app_url'), config('core.url')] as $referer) {
                $data = $this->callRaw($url, $cookie, $referer);
                if ($data !== null) {
                    return $data;
                }
            }

            return null;
        };
        if ($ttl <= 0) {
            return $haal();
        }
        $data = Cache::remember($sleutel, $ttl, fn () => $haal() ?? ['__leeg' => true]);

        return isset($data['__leeg']) ? null : $data;
    }

    /**
     * POST naar de CORE-API namens de ingelogde gebruiker. Haalt eerst een vers
     * XSRF-token op bij CORE (/sanctum/csrf-cookie) zodat de eigen XSRF-cookie van
     * deze app (zelfde naam) nooit in de weg zit.
     */
    public function apiPost(string $endpoint, array $data): ?array
    {
        $cookie = implode('; ', array_filter(explode('; ', $this->coreCookies()), fn ($c) => ! str_starts_with($c, 'XSRF-TOKEN=')));
        if ($cookie === '') {
            return null;
        }
        $xsrf = null;
        try {
            $res = Http::timeout(8)->withHeaders(['Accept' => 'application/json', 'Referer' => config('core.app_url').'/', 'Cookie' => $cookie])->get(config('core.url').'/sanctum/csrf-cookie');
            foreach ($res->cookies()->toArray() as $c) {
                if (($c['Name'] ?? '') === 'XSRF-TOKEN') {
                    $xsrf = urldecode((string) $c['Value']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CORE csrf-cookie mislukt: '.$e->getMessage());
        }
        if (! $xsrf) {
            return null;
        }
        $cookie .= '; XSRF-TOKEN='.rawurlencode($xsrf);
        foreach ([config('core.app_url'), config('core.url')] as $referer) {
            try {
                $res = Http::timeout(10)->withHeaders([
                    'Accept' => 'application/json',
                    'Referer' => $referer.'/',
                    'Cookie' => $cookie,
                    'X-XSRF-TOKEN' => $xsrf,
                ])->post(config('core.url').$endpoint, $data);
            } catch (\Throwable $e) {
                Log::warning('CORE-post mislukt: '.$e->getMessage(), ['endpoint' => $endpoint]);
                continue;
            }
            if ($res->successful()) {
                $this->vergeetApiCache();

                return $res->json() ?: ['ok' => true];
            }
            Log::warning('CORE-post geweigerd: '.$res->status(), ['endpoint' => $endpoint, 'body' => mb_substr($res->body(), 0, 300)]);
        }

        return null;
    }

    /** API-cache van deze gebruiker leegmaken (na een wijziging). */
    public function vergeetApiCache(): void
    {
        // Cache-sleutels zijn per gebruiker+url; zonder tags verhogen we een generatie-nummer
        $gid = (int) (core_gebruiker()['id'] ?? 0);
        Cache::put('core_api_gen:'.$gid, time(), 3600);
    }

    /** Alle actieve medewerkerkaarten uit CORE (GET /api/employees, gepagineerd). null bij storing. */
    public function employees(): ?array
    {
        if ($this->fakeUser()) {
            return [
                ['id' => 77, 'name' => 'Anne Vasseur', 'email' => 'anne.vasseur@boels.nl', 'phone' => '06-11111111', 'employee_number' => '17301', 'function' => 'Binnendienst', 'depot' => 'Rotterdam', 'area' => 'West', 'active' => true],
                ['id' => 42, 'name' => 'Justin Bikker', 'email' => 'justin.bikker@boels.nl', 'phone' => '06-22222222', 'employee_number' => '17375', 'function' => 'Monteur', 'depot' => 'Rotterdam', 'area' => 'West', 'active' => true],
                ['id' => 12, 'name' => 'Wim Kolker', 'email' => 'wim.kolker@boels.nl', 'phone' => '06-33333333', 'employee_number' => '17386', 'function' => 'Servicedesk', 'depot' => 'Chemelot', 'area' => 'Zuid', 'active' => true],
                ['id' => 1, 'name' => 'Test Gebruiker (lokaal)', 'email' => 'test@boels.nl', 'phone' => '06-00000000', 'employee_number' => '99999', 'function' => 'Beheer', 'depot' => 'Rotterdam', 'area' => 'West', 'active' => true],
            ];
        }
        $cookie = $this->coreCookies();
        if ($cookie === '') {
            return null;
        }
        $alles = [];
        $pagina = 1;
        do {
            $data = null;
            foreach ([config('core.app_url'), config('core.url')] as $referer) {
                $data = $this->callRaw('/api/employees?per_page=200&page='.$pagina, $cookie, $referer);
                if ($data !== null) {
                    break;
                }
            }
            if (! is_array($data)) {
                return $pagina === 1 ? null : $alles;
            }
            $rijen = $data['data'] ?? (array_is_list($data) ? $data : []);
            foreach ($rijen as $r) {
                $alles[] = $r;
            }
            $laatste = (int) ($data['last_page'] ?? 1);
            $pagina++;
        } while ($pagina <= $laatste && $pagina < 50);

        return $alles;
    }

    /**
     * Rol-slugs van deze gebruiker in deze app. Super-admins van CORE krijgen
     * alle rollen (zij kiezen op het rolkeuzescherm). Onbekende slugs worden
     * genegeerd; CORE gebruikt streepjes, wij accepteren beide schrijfwijzen.
     */
    public function rollen(?array $access): array
    {
        $bekend = array_keys(config('core.rollen'));
        if (! is_array($access)) {
            return [];
        }
        if (! empty($access['is_super_admin'])) {
            return $bekend;
        }
        $slugs = [];
        foreach ($access['roles'] ?? [] as $rol) {
            $slug = str_replace('-', '_', (string) ($rol['slug'] ?? ''));
            if (($rol['scope'] ?? 'app') === 'app' && in_array($slug, $bekend, true)) {
                $slugs[] = $slug;
            }
        }

        return array_values(array_filter($bekend, fn ($s) => in_array($s, $slugs, true)));
    }

    /** Alles wat over de CORE-gebruiker in de sessie staat weggooien. */
    public function vergeet(): void
    {
        foreach (['core_user', 'core_access'] as $k) {
            $this->request->session()->forget([$k, $k.'_at']);
        }
    }

    // ------------------------------------------------------------------

    /** Alleen de cookies van CORE doorsturen (sessie + XSRF), niet onze eigen sessiecookie. */
    private function coreCookies(): string
    {
        $delen = [];
        foreach (explode(';', (string) $this->request->header('Cookie', '')) as $deel) {
            $deel = trim($deel);
            $naam = strstr($deel, '=', true);
            if ($naam !== false && (str_starts_with($naam, 'boels_core') || $naam === 'XSRF-TOKEN')) {
                $delen[] = $deel;
            }
        }

        return implode('; ', $delen);
    }

    private function relay(string $endpoint, string $cacheKey, bool $force): ?array
    {
        $session = $this->request->session();
        $ttl = (int) config('core.cache_seconds', 300);
        if (! $force && $session->has($cacheKey) && $session->has($cacheKey.'_at')
            && time() - (int) $session->get($cacheKey.'_at') < $ttl) {
            return $session->get($cacheKey);
        }

        $cookie = $this->coreCookies();
        $data = null;
        if ($cookie !== '') {
            // Referer = onze eigen URL (staat in CORE's SANCTUM_STATEFUL_DOMAINS);
            // valt terug op CORE's eigen URL als dat (nog) niet zo is.
            foreach ([config('core.app_url'), config('core.url')] as $referer) {
                $data = $this->call($endpoint, $cookie, $referer);
                if ($data !== null) {
                    break;
                }
            }
        }

        $session->put($cacheKey, $data);
        $session->put($cacheKey.'_at', time());

        return $data;
    }

    private function call(string $endpoint, string $cookie, string $referer): ?array
    {
        $data = $this->callRaw($endpoint, $cookie, $referer);
        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        return is_array($data) ? $data : null;
    }

    /** Zoals call(), maar zonder de 'data'-uitpak (paginatie-metadata blijft beschikbaar). */
    private function callRaw(string $endpoint, string $cookie, string $referer): ?array
    {
        try {
            $res = Http::timeout(8)->withHeaders([
                'Accept' => 'application/json',
                'Referer' => $referer.'/',
                'Cookie' => $cookie,
            ])->get(config('core.url').$endpoint);
        } catch (\Throwable $e) {
            Log::warning('CORE-relay mislukt: '.$e->getMessage(), ['endpoint' => $endpoint]);

            return null;
        }
        if ($res->status() !== 200) {
            return null;
        }
        $data = $res->json();

        return is_array($data) ? $data : null;
    }

    private function fakeUser(): ?array
    {
        if (! app()->environment('local') || ! config('core.dev_fake_user')) {
            return null;
        }

        return [
            'id' => 1, 'name' => 'Test Gebruiker (lokaal)', 'email' => 'test@boels.nl', 'employee_id' => 1,
            'is_super_admin' => true, 'allowed_areas' => ['West'], 'allowed_depots' => ['Rotterdam'],
        ];
    }
}
