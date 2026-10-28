<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EuisisReferral;
use App\Services\Euisis\EuisisClient;
use App\Services\Euisis\EuisisException;
use App\Services\Euisis\EuisisSync;
use App\Support\CaseScope;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Links a case's respondent organization and its applicant (or referred
 * complainant) to EUISIS records (docs/euisis-integration.md, Flow 1). The
 * free-text names recorded on the case are never changed.
 */
class EuisisCaseLinkController extends Controller
{
    public function organization(Request $request, int $id): RedirectResponse
    {
        $this->writableCase($id);
        $data = $request->validateWithBag('euisis', [
            'organization_uuid' => ['nullable', 'uuid', 'exists:euisis_organizations,uuid'],
        ]);

        DB::table('court_cases')->where('id', $id)->update([
            'respondent_organization_uuid' => $data['organization_uuid'] ?? null,
            'updated_at' => now(),
        ]);

        return $this->back($id, __('euisis.panel.organization_linked'));
    }

    public function employee(Request $request, int $id, EuisisClient $client, EuisisSync $sync): RedirectResponse
    {
        $case = $this->writableCase($id);
        $data = $request->validateWithBag('euisis', ['employee_number' => ['required', 'string', 'max:60']]);
        $referral = EuisisReferral::query()->where('court_case_id', $id)->first();

        if ($case->applicant_id === null && $referral === null) {
            return $this->fail($id, __('euisis.panel.no_party'));
        }
        if (! $client->enabled()) {
            return $this->fail($id, __('euisis.panel.disabled'));
        }

        try {
            $matches = $client->employeesByNumber(trim($data['employee_number']));
            if ($matches === []) {
                return $this->fail($id, __('euisis.panel.employee_not_found', ['number' => $data['employee_number']]));
            }
            $employee = $sync->cacheEmployee($matches[0]);
        } catch (EuisisException $e) {
            Log::warning('EUISIS employee lookup failed', ['case' => $id, 'error' => $e->getMessage(), 'request_id' => $e->requestId]);

            return $this->fail($id, __('euisis.panel.unreachable'));
        }

        if ($case->applicant_id !== null) {
            DB::table('applicants')->where('id', $case->applicant_id)->update(['euisis_employee_uuid' => $employee->uuid, 'updated_at' => now()]);
        } else {
            $referral->forceFill(['complainant_employee_uuid' => $employee->uuid])->save();
        }

        return $this->back($id, __('euisis.panel.employee_linked', ['name' => $employee->displayName()]));
    }

    private function back(int $id, string $message): RedirectResponse
    {
        return redirect()->to(route('cases.show', $id).'#euisis-panel')->with('euisis_status', $message);
    }

    private function fail(int $id, string $message): RedirectResponse
    {
        return redirect()->to(route('cases.show', $id).'#euisis-panel')->withErrors(['employee_number' => $message], 'euisis')->withInput();
    }

    /** The case within the staff member's team scope and not frozen by a published decision (CaseScope). */
    private function writableCase(int $id): object
    {
        $case = DB::table('court_cases')->where('id', $id)->first();
        abort_unless($case !== null, 404);
        CaseScope::ensure($case);
        if (CaseScope::isLocked($case)) {
            throw new HttpResponseException(back()->with('error', __('cases.show.actions_locked')));
        }

        return $case;
    }
}
