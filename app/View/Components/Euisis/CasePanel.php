<?php

declare(strict_types=1);

namespace App\View\Components\Euisis;

use App\Models\EuisisEmployee;
use App\Models\EuisisOrganization;
use App\Models\EuisisPosition;
use App\Models\EuisisReferral;
use App\Services\Euisis\EuisisClient;
use App\Services\Euisis\EuisisSync;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

/**
 * The EUISIS panel on the admin case page: where a referred case came from,
 * the respondent organization as EUISIS knows it, and the EUISIS employee
 * record of the applicant or complainant (docs/euisis-integration.md).
 */
class CasePanel extends Component
{
    public ?object $case;

    public ?EuisisReferral $referral;

    public ?EuisisOrganization $organization;

    public ?EuisisEmployee $employee;

    public ?EuisisPosition $position;

    public ?EuisisOrganization $employeeOrganization;

    public bool $enabled;

    public ?string $lastRun;

    public ?string $lastError;

    public bool $hasOrganizations;

    public function __construct(public int $caseId)
    {
        $this->case = DB::table('court_cases')->where('id', $caseId)->first(['id', 'applicant_id', 'source', 'respondent_organization_uuid']);
        $this->referral = EuisisReferral::query()->where('court_case_id', $caseId)->first();
        $this->organization = $this->case?->respondent_organization_uuid !== null
            ? EuisisOrganization::query()->find($this->case->respondent_organization_uuid) : null;

        $employeeUuid = $this->case?->applicant_id !== null
            ? DB::table('applicants')->where('id', $this->case->applicant_id)->value('euisis_employee_uuid')
            : $this->referral?->complainant_employee_uuid;
        $this->employee = $employeeUuid !== null ? EuisisEmployee::query()->find($employeeUuid) : null;
        $this->position = $this->employee?->position_uuid !== null ? EuisisPosition::query()->find($this->employee->position_uuid) : null;
        $this->employeeOrganization = $this->employee?->organization_uuid !== null ? EuisisOrganization::query()->find($this->employee->organization_uuid) : null;

        $sync = app(EuisisSync::class);
        $this->enabled = app(EuisisClient::class)->enabled();
        $this->lastRun = $sync->state(EuisisSync::LAST_RUN_KEY);
        $this->lastError = $sync->state(EuisisSync::LAST_ERROR_KEY);
        $this->hasOrganizations = EuisisOrganization::query()->exists();
    }

    public function shouldRender(): bool
    {
        return $this->case !== null && ($this->enabled || $this->referral !== null || $this->organization !== null || $this->employee !== null);
    }

    /** Organizations to pick from, current ones first by name. */
    public function organizations()
    {
        return EuisisOrganization::query()->orderByRaw("CASE WHEN status IS NULL OR status = 'active' THEN 0 ELSE 1 END")->orderBy('name_en')->get(['uuid', 'name_en', 'name_am', 'status', 'code']);
    }

    public function render(): View
    {
        return view('components.euisis.case-panel');
    }
}
