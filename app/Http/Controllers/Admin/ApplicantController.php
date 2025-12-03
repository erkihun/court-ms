<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    /**
     * List applicants with optional search.
     */
    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());

        $status = $request->string('status')->toString();

        $applicants = Applicant::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('first_name', 'like', "%{$q}%")
                        ->orWhere('middle_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('organization_name', 'like', "%{$q}%")
                        ->orWhere('position', 'like', "%{$q}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.applicants.index', compact('applicants', 'q', 'status'));
    }

    public function updateStatus(Request $request, Applicant $applicant)
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $applicant->update(['is_active' => $data['is_active']]);

        $message = $applicant->is_active ? 'Applicant activated.' : 'Applicant deactivated.';
        return back()->with('success', $message);
    }
}
