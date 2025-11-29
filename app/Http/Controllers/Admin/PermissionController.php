<?php
// app/Http/Controllers/PermissionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->get('q', ''));
        $perPage = (int) $request->input('perPage', 15);

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
            ->withQueryString();

        return view('admin.permissions.index', compact('permissions', 'q', 'perPage'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
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

        return redirect()->route('permissions.index')->with('ok', __('Permission created.'));
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
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

        return redirect()->route('permissions.index')->with('ok', __('Permission updated.'));
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return redirect()->route('permissions.index')->with('ok', __('Permission deleted.'));
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
