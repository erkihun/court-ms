<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ApplicantSessionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('applicant');
        abort_if($user === null, 401);

        $currentId = $request->session()->getId();
        $records = DB::table('sessions')
            ->where('user_id', $user->getAuthIdentifier())
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity', 'payload'])
            ->filter(fn (object $session): bool => (string) $session->id === (string) $currentId || $this->belongsToApplicant($session));

        $sessions = $records->map(fn (object $session): object => $this->present($session, $currentId))->values();

        if (! $sessions->contains(fn (object $session): bool => $session->is_current)) {
            $sessions->prepend($this->presentCurrent($request));
        }

        return view('applicant.profile.sessions', compact('sessions'));
    }

    public function destroy(Request $request, string $session): RedirectResponse
    {
        $user = $request->user('applicant');
        abort_if($user === null, 401);
        abort_if(hash_equals($request->session()->getId(), $session), 422, __('auth.session_current_cannot_revoke'));

        $record = DB::table('sessions')
            ->where('id', $session)
            ->where('user_id', $user->getAuthIdentifier())
            ->first(['id', 'payload']);

        if ($record !== null && $this->belongsToApplicant($record)) {
            DB::table('sessions')->where('id', $session)->delete();
        }

        return back()->with('status', __('auth.session_revoked'));
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        $user = $request->user('applicant');
        abort_if($user === null, 401);

        $currentId = $request->session()->getId();
        $sessionIds = DB::table('sessions')
            ->where('user_id', $user->getAuthIdentifier())
            ->get(['id', 'payload'])
            ->filter(fn (object $session): bool => (string) $session->id !== (string) $currentId && $this->belongsToApplicant($session))
            ->pluck('id');

        if ($sessionIds->isNotEmpty()) {
            DB::table('sessions')->whereIn('id', $sessionIds->all())->delete();
        }

        return back()->with('status', __('auth.other_sessions_revoked'));
    }

    private function present(object $session, string $currentId): object
    {
        $hints = $this->payloadHints((string) $session->payload);
        $userAgent = (string) $session->user_agent;

        return (object) [
            'id' => $session->id,
            'ip_address' => $session->ip_address,
            'user_agent' => $userAgent,
            'device_name' => ($hints ? $this->deviceFromHints($hints) : null) ?? $this->deviceName($userAgent),
            'browser_name' => ($hints ? $this->browserFromHints($hints) : null) ?? $this->browserName($userAgent),
            'location' => __('auth.unknown_location'),
            'is_current' => hash_equals((string) $currentId, (string) $session->id),
            'last_active_at' => now()->setTimestamp((int) $session->last_activity),
        ];
    }

    private function presentCurrent(Request $request): object
    {
        $userAgent = (string) $request->userAgent();
        $hints = $request->session()->get('client_hints');
        $hints = is_array($hints) ? $hints : null;

        return (object) [
            'id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_name' => ($hints ? $this->deviceFromHints($hints) : null) ?? $this->deviceName($userAgent),
            'browser_name' => ($hints ? $this->browserFromHints($hints) : null) ?? $this->browserName($userAgent),
            'location' => __('auth.unknown_location'),
            'is_current' => true,
            'last_active_at' => now(),
        ];
    }

    private function belongsToApplicant(object $session): bool
    {
        $payload = base64_decode((string) $session->payload, true);

        if ($payload === false) {
            return false;
        }

        $data = @unserialize($payload, ['allowed_classes' => false]);

        return is_array($data) && ($data['_auth_guard'] ?? null) === 'applicant';
    }

    /**
     * @return array{platform?: string, platform_version?: string, brands?: string}|null
     */
    private function payloadHints(string $payload): ?array
    {
        $decoded = base64_decode($payload, true);
        $data = $decoded === false ? null : @unserialize($decoded, ['allowed_classes' => false]);

        return is_array($data) && is_array($data['client_hints'] ?? null) ? $data['client_hints'] : null;
    }

    /**
     * @param array{platform?: string, platform_version?: string, brands?: string} $hints
     */
    private function deviceFromHints(array $hints): ?string
    {
        $platform = (string) ($hints['platform'] ?? '');

        if ($platform === '') {
            return null;
        }

        if ($platform === 'Windows') {
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
     * @param array{platform?: string, platform_version?: string, brands?: string} $hints
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
