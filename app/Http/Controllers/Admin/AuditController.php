<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditController extends Controller
{
    public function index()
    {
        $path = base_path('docs/system-audit.md');
        $content = File::exists($path) ? File::get($path) : 'No audit document found.';
        $html = Str::markdown($content);

        $audits = DB::table('case_audits')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $systemAudits = DB::table('system_audits')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        // Enrich system audits with actor names
        $systemUserIds = $systemAudits->pluck('user_id')->filter()->unique();
        $systemUserNames = $systemUserIds->isNotEmpty()
            ? DB::table('users')->whereIn('id', $systemUserIds)->pluck('name', 'id')
            : collect();

        foreach ($systemAudits as $s) {
            $s->actor_name = $s->user_id ? ($systemUserNames[$s->user_id] ?? null) : null;
        }

        $systemAuditsRecent = $systemAudits->take(5);

        // Build unified audit stream (system only here) ordered by time desc
        $combinedAudits = collect();

        foreach ($systemAudits as $s) {
            $ctx = json_decode($s->context ?? '[]', true) ?? [];
            $inputPairs = collect($ctx['input'] ?? [])
                ->map(function ($v, $k) {
                    $val = is_array($v) ? '[array]' : (string) $v;
                    return "{$k}={$val}";
                })
                ->take(5)
                ->implode(', ');
            $inputKeys = collect($ctx['input'] ?? [])->keys()->take(10)->implode(', ');

            $noteParts = [];
            if (!empty($ctx['path'])) {
                $noteParts[] = "Path: {$ctx['path']}";
            }
            if ($inputKeys !== '') {
                $noteParts[] = "Fields: {$inputKeys}";
            } elseif ($inputPairs !== '') {
                $noteParts[] = "Input: {$inputPairs}";
            }

            $friendlyTarget = $s->route
                ? str_replace('.', ' › ', $s->route)
                : ($s->module ? ucfirst($s->module) : 'System');

            $combinedAudits->push((object) [
                'created_at' => $s->created_at,
                'target'     => $friendlyTarget,
                'action'     => ($s->method ? strtoupper($s->method) . ' ' : '') . ($s->action ?? $s->route ?? 'event'),
                'actor'      => $s->actor_name ?? ($s->user_id ? "User #{$s->user_id}" : 'System'),
                'note'       => implode(' | ', $noteParts) ?: null,
            ]);
        }

        $combinedAudits = $combinedAudits->sortByDesc('created_at')->values();

        return view('admin.audit', [
            'audit' => $content,
            'auditHtml' => $html,
            'audits' => $audits,
            'systemAudits' => $systemAudits,
            'systemAuditsRecent' => $systemAuditsRecent,
            'combinedAudits' => $combinedAudits,
        ]);
    }
}
