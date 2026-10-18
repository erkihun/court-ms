<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Support\EthiopianDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantController extends Controller
{
    /**
     * List applicants with optional search.
     */
    public function index(Request $request): Response
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
            ->withQueryString()
            ->through(fn (Applicant $applicant): array => [
                'id' => (int) $applicant->id,
                'name' => (string) $applicant->full_name,
                'nationalId' => $applicant->national_id_number,
                'email' => (string) $applicant->email,
                'phone' => $applicant->phone,
                'position' => $applicant->position,
                'organization' => $applicant->organization_name,
                'active' => (bool) $applicant->is_active,
                'verified' => $applicant->hasVerifiedEmail(),
                'created' => EthiopianDate::format($applicant->created_at),
            ]);

        return Inertia::render('Admin/Applicants/Index', [
            'applicants' => $applicants,
            'filters' => ['q' => $q, 'status' => in_array($status, ['active', 'inactive'], true) ? $status : ''],
            'canManage' => (bool) $request->user()?->hasPermission('applicants.manage'),
        ]);
    }

    public function updateStatus(Request $request, Applicant $applicant): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $applicant->update(['is_active' => $data['is_active']]);

        $message = [
            'key' => $applicant->is_active ? 'messages.success.activated' : 'messages.success.deactivated',
            'replace' => ['resource' => __('messages.resources.applicant')],
        ];

        return back()->with('success', $message);
    }
}
