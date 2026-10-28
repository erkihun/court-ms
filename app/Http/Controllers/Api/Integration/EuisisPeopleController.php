<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Models\IntegrationClient;
use App\Services\Euisis\EmployeeCourtAccess;
use App\Services\Euisis\EmployeeCourtAccessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Flow 4 (docs/euisis-integration.md): the EUISIS employee portal's court
 * section. Both calls are POSTs so a national ID never sits in a URL or an
 * access log.
 */
class EuisisPeopleController extends Controller
{
    /** The employee's court summary, or `linked: false` when they have no court account yet. */
    public function summary(Request $request, EmployeeCourtAccess $access): JsonResponse
    {
        $employee = $request->validate($this->identityRules())['employee'];

        try {
            $applicant = $access->find($employee);
        } catch (EmployeeCourtAccessException $e) {
            return $this->refuse($request, 409, $e);
        }

        return response()->json(['data' => $applicant === null
            ? ['linked' => false]
            : ['linked' => true, ...$access->summary($applicant)]]);
    }

    /** A one-time URL that signs the employee into the applicant portal (opening their court account on first use). */
    public function signInTicket(Request $request, EmployeeCourtAccess $access): JsonResponse
    {
        $data = $request->validate([
            ...$this->identityRules(),
            'employee.first_name' => ['required', 'string', 'max:100'],
            'employee.middle_name' => ['nullable', 'string', 'max:100'],
            'employee.last_name' => ['required', 'string', 'max:100'],
            'employee.gender' => ['nullable', 'string', 'max:20'],
            'employee.phone' => ['nullable', 'string', 'max:30'],
            'employee.position' => ['nullable', 'string', 'max:150'],
            'employee.organization_name' => ['nullable', 'string', 'max:255'],
            'employee.address' => ['nullable', 'string', 'max:255'],
            'target' => ['required', 'string', 'max:40', 'regex:/^(dashboard|cases|new_case|notifications|profile|case:\d+)$/'],
        ]);

        try {
            $applicant = $access->findOrOpen($data['employee']);
            $ticket = $access->issueTicket($applicant, $data['target'], $this->client($request));
        } catch (EmployeeCourtAccessException $e) {
            return $this->refuse($request, $e->errorCode === 'unknown_target' ? 404 : 409, $e);
        }

        return response()->json(['data' => $ticket], 201);
    }

    /** @return array<string, list<string>> */
    private function identityRules(): array
    {
        return [
            'employee' => ['required', 'array'],
            'employee.uuid' => ['required', 'uuid'],
            'employee.national_id' => ['nullable', 'string', 'max:32'],
            'employee.email' => ['nullable', 'email', 'max:255'],
        ];
    }

    private function client(Request $request): ?IntegrationClient
    {
        $client = $request->attributes->get('integration_client');

        return $client instanceof IntegrationClient ? $client : null;
    }

    private function refuse(Request $request, int $status, EmployeeCourtAccessException $e): JsonResponse
    {
        return response()->json([
            'message' => $e->getMessage(),
            'error_code' => $e->errorCode,
            'request_id' => $request->attributes->get('request_id'),
        ], $status);
    }
}
