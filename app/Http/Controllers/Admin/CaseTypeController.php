<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CaseTypeController extends Controller
{
    /**
     * List case types (+ usage count) with optional search.
     */
    public function index(Request $request)
    {
        $this->authorizeTypes();

        $q = trim($request->string('q')->toString());

        $types = DB::table('case_types as ct')
            ->when($q !== '', fn($w) => $w->where('ct.name', 'like', "%{$q}%"))
            ->leftJoin('court_cases as c', 'c.case_type_id', '=', 'ct.id')
            ->groupBy('ct.id', 'ct.name')
            ->select('ct.id', 'ct.name', DB::raw('COUNT(c.id) as cases_count'))
            ->orderBy('ct.name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.cases.types.index', compact('types', 'q'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $this->authorizeTypes();
        return view('admin.cases.types.create');
    }

    /**
     * Store a new case type.
     */
    public function store(Request $request)
    {
        $this->authorizeTypes();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('case_types', 'name')],
        ]);

        $row = ['name' => $data['name']];
        if (Schema::hasColumn('case_types', 'created_at')) $row['created_at'] = now();
        if (Schema::hasColumn('case_types', 'updated_at')) $row['updated_at'] = now();

        DB::table('case_types')->insert($row);

        return redirect()->route('case-types.index')->with('success', 'Case type created.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $this->authorizeTypes();

        $type = DB::table('case_types')->where('id', $id)->first();
        abort_if(!$type, 404);

        return view('admin.cases.types.edit', compact('type'));
    }

    /**
     * Update case type.
     */
    public function update(Request $request, int $id)
    {
        $this->authorizeTypes();

        $type = DB::table('case_types')->where('id', $id)->first();
        abort_if(!$type, 404);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('case_types', 'name')->ignore($id),
            ],
        ]);

        $row = ['name' => $data['name']];
        if (Schema::hasColumn('case_types', 'updated_at')) $row['updated_at'] = now();

        DB::table('case_types')->where('id', $id)->update($row);

        return redirect()->route('case-types.index')->with('success', 'Case type updated.');
    }

    /**
     * Delete case type (blocked if in use).
     */
    public function destroy(int $id)
    {
        $this->authorizeTypes();

        $type = DB::table('case_types')->where('id', $id)->first();
        abort_if(!$type, 404);

        $inUse = DB::table('court_cases')->where('case_type_id', $id)->count();
        if ($inUse > 0) {
            return back()->with('error', 'Cannot delete: this case type is used by ' . $inUse . ' case(s).');
        }

        DB::table('case_types')->where('id', $id)->delete();

        return back()->with('success', 'Case type deleted.');
    }

    /**
     * Permission gate for managing case types.
     * Reuse your helper style from other controllers.
     */
    private function authorizeTypes(): void
    {
        if (!function_exists('userHasPermission') || !userHasPermission('cases.types')) {
            abort(403, 'You do not have permission: cases.types');
        }
    }
}
