<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class ActiveSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $currentId = $request->session()->getId();
        $sessions = $this->staffSessions($request)->sortByDesc('last_activity')->values()->map(function (object $session) use ($currentId): object {
            $hints = $this->payloadHints((string) $session->payload);

            return (object) [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'device_name' => ($hints ? $this->deviceFromHints($hints) : null) ?? $this->deviceName((string) $session->user_agent),
                'browser_name' => ($hints ? $this->browserFromHints($hints) : null) ?? $this->browserName((string) $session->user_agent),
                'location' => __('auth.unknown_location'),
                'is_current' => hash_equals((string) $currentId, (string) $session->id),
                'last_active_at' => now()->setTimestamp((int) $session->last_activity),
            ];
        });

        $liveUserAgent = (string) $request->userAgent();
        $liveHints = $this->requestHints($request) ?? $request->session()->get('client_hints');
        $liveHints = is_array($liveHints) ? $liveHints : null;
        $liveDevice = ($liveHints ? $this->deviceFromHints($liveHints) : null) ?? $this->deviceName($liveUserAgent);
        $liveBrowser = ($liveHints ? $this->browserFromHints($liveHints) : null) ?? $this->browserName($liveUserAgent);

        $sessions = $sessions->map(function (object $session) use ($liveUserAgent, $liveDevice, $liveBrowser): object {
            if ($session->is_current) {
                $session->user_agent = $liveUserAgent;
                $session->device_name = $liveDevice;
                $session->browser_name = $liveBrowser;
            }

            return $session;
        });

        if (! $sessions->contains(fn (object $session): bool => $session->is_current)) {
            $sessions->prepend((object) [
                'id' => $currentId,
                'ip_address' => $request->ip(),
                'user_agent' => $liveUserAgent,
                'device_name' => $liveDevice,
                'browser_name' => $liveBrowser,
                'location' => __('auth.unknown_location'),
                'is_current' => true,
                'last_active_at' => now(),
            ]);
        }

        return Inertia::render('Admin/Profile/Sessions', [
            'databaseDriver' => config('session.driver') === 'database',
            'sessions' => $sessions->map(fn (object $session): array => [
                // Session ids never reach the browser; rows are addressed by a keyed hash.
                'key' => $this->publicKey((string) $session->id),
                'deviceName' => $session->device_name,
                'browserName' => $session->browser_name,
                'location' => $session->location,
                'ipAddress' => $session->ip_address,
                'isCurrent' => $session->is_current,
                'lastActive' => $session->last_active_at->diffForHumans(),
                'lastActiveAt' => $session->last_active_at->format('n/j/Y H:i'),
            ])->values(),
        ]);
    }

    public function destroy(Request $request, string $session): RedirectResponse
    {
        abort_if(hash_equals($this->publicKey($request->session()->getId()), $session), 422, __('auth.session_current_cannot_revoke'));

        $record = $this->staffSessions($request)
            ->first(fn (object $record): bool => hash_equals($this->publicKey((string) $record->id), $session));

        if ($record !== null) {
            DB::table('sessions')->where('id', $record->id)->delete();
        }

        return back()->with('status', __('auth.session_revoked'));
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        $currentId = (string) $request->session()->getId();
        $ids = $this->staffSessions($request)
            ->reject(fn (object $record): bool => (string) $record->id === $currentId)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('sessions')->whereIn('id', $ids->all())->delete();
        }

        return back()->with('status', __('auth.other_sessions_revoked'));
    }

    /**
     * Sessions of this staff account. The table is shared with the applicant
     * portal and user_id is only unique per guard, so rows written by the
     * applicant or respondent guard (marked by UseGuard) are excluded.
     *
     * @return Collection<int, object>
     */
    private function staffSessions(Request $request): Collection
    {
        return DB::table('sessions')
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->get(['id', 'ip_address', 'user_agent', 'last_activity', 'payload'])
            ->reject(fn (object $session): bool => in_array($this->payloadValue((string) $session->payload, '_auth_guard'), ['applicant', 'respondent'], true))
            ->values();
    }

    /** Stable, non-reversible handle for a session row (the raw id would let a script replay the session). */
    private function publicKey(string $sessionId): string
    {
        return substr(hash_hmac('sha256', $sessionId, (string) config('app.key')), 0, 40);
    }

    private function payloadValue(string $payload, string $key): mixed
    {
        $decoded = $payload === '' ? false : base64_decode($payload, true);
        $data = $decoded === false ? null : @unserialize($decoded, ['allowed_classes' => false]);

        return is_array($data) ? ($data[$key] ?? null) : null;
    }

    /**
     * @return array{platform?: string, platform_version?: string, brands?: string}|null
     */
    private function requestHints(Request $request): ?array
    {
        if (! $request->hasHeader('Sec-CH-UA-Platform')) {
            return null;
        }

        return array_filter([
            'platform' => trim((string) $request->header('Sec-CH-UA-Platform'), '" '),
            'platform_version' => trim((string) $request->header('Sec-CH-UA-Platform-Version'), '" '),
            'brands' => (string) ($request->header('Sec-CH-UA-Full-Version-List') ?: $request->header('Sec-CH-UA')),
        ]);
    }

    /**
     * Extract client hints captured by CaptureClientHints from a stored session payload.
     *
     * @return array{platform?: string, platform_version?: string, brands?: string}|null
     */
    private function payloadHints(string $payload): ?array
    {
        if ($payload === '') {
            return null;
        }

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return null;
        }

        $data = @unserialize($decoded, ['allowed_classes' => false]);

        return is_array($data) && is_array($data['client_hints'] ?? null) ? $data['client_hints'] : null;
    }

    /**
     * @param  array{platform?: string, platform_version?: string, brands?: string}  $hints
     */
    private function deviceFromHints(array $hints): ?string
    {
        $platform = (string) ($hints['platform'] ?? '');

        if ($platform === '') {
            return null;
        }

        if ($platform === 'Windows') {
            // Sec-CH-UA-Platform-Version >= 13 means Windows 11.
            $major = (int) strtok((string) ($hints['platform_version'] ?? ''), '.');

            return match (true) {
                $major >= 13 => 'Windows 11',
                $major >= 1 => 'Windows 10',
                default => 'Windows',
            };
        }

        return $platform;
    }

    /**
     * @param  array{platform?: string, platform_version?: string, brands?: string}  $hints
     */
    private function browserFromHints(array $hints): ?string
    {
        if (preg_match_all('/"([^"]+)";v="([^"]+)"/', (string) ($hints['brands'] ?? ''), $matches, PREG_SET_ORDER) === false) {
            return null;
        }

        $brands = [];

        foreach ($matches as $match) {
            if (stripos($match[1], 'brand') === false) {
                $brands[$match[1]] = $match[2];
            }
        }

        foreach (['Microsoft Edge', 'Opera', 'Brave', 'Vivaldi', 'Google Chrome', 'Chromium'] as $name) {
            if (isset($brands[$name])) {
                return ($name === 'Google Chrome' ? 'Chrome' : $name).' '.$brands[$name];
            }
        }

        $name = array_key_first($brands);

        return $name === null ? null : $name.' '.$brands[$name];
    }

    private function deviceName(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'iPhone') => 'iPhone',
            str_contains($userAgent, 'iPad') => 'iPad',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Mac') => 'macOS',
            str_contains($userAgent, 'CrOS') => 'ChromeOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown device',
        };
    }

    private function browserName(string $userAgent): string
    {
        $patterns = [
            '/Edg(?:A|iOS)?\/([0-9]+)/i' => 'Microsoft Edge',
            '/Firefox\/([0-9]+)/i' => 'Firefox',
            '/OPR\/([0-9]+)/i' => 'Opera',
            '/Chrome\/([0-9]+)/i' => 'Chrome',
            '/Version\/([0-9]+).*Safari/i' => 'Safari',
        ];

        foreach ($patterns as $pattern => $name) {
            if (preg_match($pattern, $userAgent, $match) === 1) {
                return $name.' '.($match[1] ?? '');
            }
        }

        return 'Browser';
    }
}
