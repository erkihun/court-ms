<?php
// app/Http/Controllers/PermissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function index(Request $request): Response
    {
        $q       = trim((string) $request->get('q', ''));
        // Bounded: an unbounded perPage would let one request load the whole table.
        $perPage = max(5, min(100, (int) $request->input('perPage', 15)));

        $permissions = Permission::query()
            ->with(['roles:id,name'])   // to render role chips
            ->withCount('roles')        // ->roles_count
            ->select('permissions.*')
            // users_count = distinct users tied to any role that has this permission
            ->selectSub(function ($sub) {
                $sub->from('role_user')
                    ->selectRaw('COUNT(DISTINCT role_user.user_id)')
                    ->whereIn('role_user.role_id', function ($q) {
                        $q->from('permission_role')
                            ->select('permission_role.role_id')
                            ->whereColumn('permission_role.permission_id', 'permissions.id');
                    });
            }, 'users_count')
            ->when($q !== '', function ($query) use ($q) {
                $term = "%{$q}%";
                $query->where(function ($w) use ($term) {
                    $w->where('name', 'like', $term)
                        ->orWhere('label', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Permission $permission): array => [
                'id' => (int) $permission->id,
                'name' => (string) $permission->name,
                'label' => $permission->label_localized,
                'description' => $permission->description_localized,
                'roles' => $permission->roles->pluck('name')->values(),
                'usersCount' => (int) $permission->users_count,
            ]);

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_.-]+$/i', 'unique:permissions,name'],
            'label'                    => ['nullable', 'string', 'max:255'],
            'description'              => ['nullable', 'string', 'max:255'],
            'label_translations'       => ['nullable', 'array'],
            'label_translations.*'     => ['nullable', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $labelTranslations = $this->collectLocaleValues((array) Arr::get($data, 'label_translations', []));
        $descriptionTranslations = $this->collectLocaleValues((array) Arr::get($data, 'description_translations', []));

        Permission::create([
            'name'                    => $data['name'],
            'label'                   => $this->fallbackTranslation($labelTranslations),
            'description'             => $this->fallbackTranslation($descriptionTranslations),
            'label_translations'      => $labelTranslations ?: null,
            'description_translations'=> $descriptionTranslations ?: null,
        ]);

        return redirect()->route('permissions.index')->with('ok', __('permissions.flash.created'));
    }

    public function edit(Permission $permission): Response
    {
        return $this->form($permission);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_.-]+$/i', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'label'                    => ['nullable', 'string', 'max:255'],
            'description'              => ['nullable', 'string', 'max:255'],
            'label_translations'       => ['nullable', 'array'],
            'label_translations.*'     => ['nullable', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $labelTranslations = $this->collectLocaleValues((array) Arr::get($data, 'label_translations', []));
        $descriptionTranslations = $this->collectLocaleValues((array) Arr::get($data, 'description_translations', []));

        $permission->update([
            'name'                    => $data['name'],
            'label'                   => $this->fallbackTranslation($labelTranslations),
            'description'             => $this->fallbackTranslation($descriptionTranslations),
            'label_translations'      => $labelTranslations ?: null,
            'description_translations'=> $descriptionTranslations ?: null,
        ]);

        return redirect()->route('permissions.index')->with('ok', __('permissions.flash.updated'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->roles()->detach();
        $permission->delete();

        return redirect()->route('permissions.index')->with('ok', __('permissions.flash.deleted'));
    }

    private function form(?Permission $permission): Response
    {
        $locales = array_values(config('app.locales', [config('app.locale', 'en')]));
        $primary = (string) config('app.locale', $locales[0] ?? 'en');

        // Stored translations win; the primary locale falls back to the plain column.
        $values = function (string $field) use ($permission, $locales, $primary): array {
            $translations = (array) ($permission?->{"{$field}_translations"} ?? []);

            return collect($locales)->mapWithKeys(fn (string $locale): array => [
                $locale => (string) ($translations[$locale] ?? ($locale === $primary ? ($permission?->{$field} ?? '') : '')),
            ])->all();
        };

        return Inertia::render('Admin/Permissions/Form', [
            'permission' => $permission === null ? null : ['id' => (int) $permission->id, 'name' => (string) $permission->name],
            'locales' => $locales,
            'primaryLocale' => $primary,
            'labels' => $values('label'),
            'descriptions' => $values('description'),
        ]);
    }

    private function collectLocaleValues(array $values): array
    {
        $locales = config('app.locales', [config('app.locale', 'en')]);
        $filtered = [];

        foreach ($locales as $locale) {
            $raw = trim((string) ($values[$locale] ?? ''));
            if ($raw === '') {
                continue;
            }
            $filtered[$locale] = $raw;
        }

        return $filtered;
    }

    private function fallbackTranslation(array $translations): ?string
    {
        if (empty($translations)) {
            return null;
        }

        $primaryLocale = config('app.locale', 'en');

        if (isset($translations[$primaryLocale])) {
            return $translations[$primaryLocale];
        }

        return reset($translations);
    }
}
