<?php

namespace App\Http\Controllers;

use App\Models\Respondent;
use App\Models\RespondentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    public function caseEvidence(int $caseId, int $evidenceId): StreamedResponse
    {
        $this->authorizeCaseAccess($caseId);

        $doc = DB::table('case_evidences')
            ->where('id', $evidenceId)
            ->where('case_id', $caseId)
            ->first();

        abort_if(!$doc, 404, 'Document not found.');

        return $this->downloadFile(
            $doc->file_path ?? $doc->path ?? null,
            $doc->title ?? $doc->label ?? null,
            $doc->mime ?? null
        );
    }

    public function caseFile(int $caseId, int $fileId): StreamedResponse
    {
        $this->authorizeCaseAccess($caseId);

        $row = DB::table('case_files')
            ->where('id', $fileId)
            ->where('case_id', $caseId)
            ->first();

        abort_if(!$row, 404, 'File not found.');

        return $this->downloadFile(
            $row->path ?? null,
            $row->label ?? null,
            $row->mime ?? null
        );
    }

    public function respondentResponse(Request $request, RespondentResponse $response): StreamedResponse
    {
        $inline = $request->boolean('inline');

        if ($this->staffCan('cases.view')) {
            return $this->downloadFile($response->pdf_path, basename((string) $response->pdf_path), null, $inline);
        }

        $applicant = auth('applicant')->user();
        abort_unless($applicant, 403);

        $ownsCase = DB::table('court_cases')
            ->where('case_number', $response->case_number)
            ->where('applicant_id', $applicant->id)
            ->exists();

        if ($ownsCase) {
            return $this->downloadFile($response->pdf_path, basename((string) $response->pdf_path), null, $inline);
        }

        $respondentId = Respondent::where('email', $applicant->email)->value('id');
        abort_if(!$respondentId || (int) $response->respondent_id !== (int) $respondentId, 403);

        return $this->downloadFile($response->pdf_path, basename((string) $response->pdf_path), null, $inline);
    }

    public function appealDocument(int $appealId, int $docId): StreamedResponse
    {
        if (!$this->staffCan('appeals.view') && !$this->staffCan('appeals.edit') && !$this->staffCan('appeals.decide')) {
            abort(403);
        }

        $doc = DB::table('appeal_documents')
            ->where('id', $docId)
            ->where('appeal_id', $appealId)
            ->first();

        abort_if(!$doc, 404, 'Document not found.');

        return $this->downloadFile(
            $doc->path ?? null,
            $doc->label ?? null,
            $doc->mime ?? null
        );
    }

    private function authorizeCaseAccess(int $caseId): void
    {
        if ($this->staffCan('cases.view')) {
            return;
        }

        if ($this->applicantOwnsCase($caseId)) {
            return;
        }

        if ($this->respondentHasViewedCase($caseId)) {
            return;
        }

        abort(403);
    }

    private function applicantOwnsCase(int $caseId): bool
    {
        $aid = auth('applicant')->id();
        if (!$aid) {
            return false;
        }

        $owner = DB::table('court_cases')->where('id', $caseId)->value('applicant_id');

        return $owner && (int) $owner === (int) $aid;
    }

    private function respondentHasViewedCase(int $caseId): bool
    {
        $respondentId = $this->actingRespondentId();
        if (!$respondentId) {
            return false;
        }

        $access = session('respondent_case_access', []);
        if (!is_array($access)) {
            return false;
        }

        $lifetime = (int) config('session.lifetime', 120);
        $threshold = now()->subMinutes($lifetime)->timestamp;
        $access = array_filter($access, fn ($ts) => is_int($ts) && $ts >= $threshold);

        session(['respondent_case_access' => $access]);

        return array_key_exists($caseId, $access);
    }

    private function actingRespondentId(): ?int
    {
        if (!session('acting_as_respondent')) {
            return null;
        }

        $applicant = auth('applicant')->user();
        if (!$applicant) {
            return null;
        }

        return Respondent::where('email', $applicant->email)->value('id');
    }

    private function staffCan(string $perm): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        if (method_exists($user, 'hasPermission') && $user->hasPermission($perm)) {
            return true;
        }

        if (function_exists('userHasPermission') && userHasPermission($perm)) {
            return true;
        }

        return false;
    }

    private function downloadFile(?string $path, ?string $name = null, ?string $mime = null, bool $inline = false): StreamedResponse
    {
        abort_if(empty($path), 404, 'File missing.');

        $disk = Storage::disk('private');
        if (!$disk->exists($path)) {
            $fallback = Storage::disk('public');
            abort_if(!$fallback->exists($path), 404, 'File missing.');
            $disk = $fallback;
        }

        $downloadName = $name ?: basename($path);
        $headers = [
            'Content-Type' => $mime ?: $disk->mimeType($path) ?: 'application/octet-stream',
        ];

        if ($inline) {
            return $disk->response($path, $downloadName, $headers);
        }

        return $disk->download($path, $downloadName, $headers);
    }
}
