<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantResponseReply;
use App\Models\Respondent;
use App\Models\RespondentResponse;
use App\Models\User;
use App\Notifications\ApplicantResponseReplyReviewed;
use App\Notifications\ApplicantResponseReplySubmitted;
use App\Notifications\RespondentResponseReviewed;
use App\Notifications\RespondentResponseSubmitted;
use App\Services\ResponseNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResponseNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_applicant_and_admin_on_respondent_response_created(): void
    {
        Notification::fake();

        [$admin, $caseId, $caseNumber, $applicant, $respondent] = $this->seedCaseWithParties();

        $response = RespondentResponse::create([
            'respondent_id' => $respondent->id,
            'case_number' => $caseNumber,
            'response_number' => 'መ/' . $caseNumber,
            'title' => 'Test response',
            'description' => 'Body',
            'pdf_path' => 'respondent/responses/test.pdf',
        ]);

        ResponseNotificationService::notifyRespondentResponseCreated($response);

        Notification::assertSentTo($admin, RespondentResponseSubmitted::class);
        Notification::assertSentTo($applicant, RespondentResponseSubmitted::class);
    }

    public function test_notifies_admin_and_respondent_on_response_reply_created(): void
    {
        Notification::fake();

        [$admin, $caseId, $caseNumber, $applicant, $respondent] = $this->seedCaseWithParties();

        $response = RespondentResponse::create([
            'respondent_id' => $respondent->id,
            'case_number' => $caseNumber,
            'response_number' => 'መ/' . $caseNumber,
            'title' => 'Test response',
            'description' => 'Body',
            'pdf_path' => 'respondent/responses/test.pdf',
        ]);

        $reply = ApplicantResponseReply::create([
            'case_id' => $caseId,
            'applicant_id' => $applicant->id,
            'respondent_response_id' => $response->id,
            'description' => 'Reply body',
            'pdf_path' => 'applicant/response-replies/test.pdf',
            'review_status' => 'awaiting_review',
        ]);

        ResponseNotificationService::notifyApplicantResponseReplyCreated($reply, $response);

        Notification::assertSentTo($admin, ApplicantResponseReplySubmitted::class);
        Notification::assertSentTo($respondent, ApplicantResponseReplySubmitted::class);
    }

    public function test_notifies_respondent_on_response_review(): void
    {
        Notification::fake();

        [, , $caseNumber, , $respondent] = $this->seedCaseWithParties();

        $response = RespondentResponse::create([
            'respondent_id' => $respondent->id,
            'case_number' => $caseNumber,
            'response_number' => 'መ/' . $caseNumber,
            'title' => 'Test response',
            'description' => 'Body',
            'pdf_path' => 'respondent/responses/test.pdf',
        ]);

        ResponseNotificationService::notifyRespondentResponseReviewed($response, 'accepted', 'ok');

        Notification::assertSentTo($respondent, RespondentResponseReviewed::class);
    }

    public function test_notifies_applicant_on_response_reply_review(): void
    {
        Notification::fake();

        [, $caseId, $caseNumber, $applicant, $respondent] = $this->seedCaseWithParties();

        $response = RespondentResponse::create([
            'respondent_id' => $respondent->id,
            'case_number' => $caseNumber,
            'response_number' => 'መ/' . $caseNumber,
            'title' => 'Test response',
            'description' => 'Body',
            'pdf_path' => 'respondent/responses/test.pdf',
        ]);

        $reply = ApplicantResponseReply::create([
            'case_id' => $caseId,
            'applicant_id' => $applicant->id,
            'respondent_response_id' => $response->id,
            'description' => 'Reply body',
            'pdf_path' => 'applicant/response-replies/test.pdf',
            'review_status' => 'awaiting_review',
        ]);

        ResponseNotificationService::notifyResponseReplyReviewed($reply, 'returned', 'fix');

        Notification::assertSentTo($applicant, ApplicantResponseReplyReviewed::class);
    }

    /**
     * Seed a minimal case with parties and an admin reviewer.
     *
     * @return array{User,int,string,Applicant,Respondent}
     */
    private function seedCaseWithParties(): array
    {
        $admin = User::factory()->create();
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'description' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('role_user')->insert([
            'user_id' => $admin->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $caseTypeId = DB::table('case_types')->insertGetId([
            'name' => 'Civil',
            'description' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $applicant = Applicant::create([
            'first_name' => 'App',
            'middle_name' => 'M',
            'last_name' => 'User',
            'gender' => 'male',
            'phone' => '2519' . rand(10000000, 99999999),
            'email' => 'applicant' . rand(10, 9999) . '@example.com',
            'password' => bcrypt('password'),
            'national_id_number' => (string) rand(1000000000000000, 9999999999999999),
            'address' => 'Address',
        ]);

        $respondent = Respondent::create([
            'first_name' => 'Resp',
            'middle_name' => 'R',
            'last_name' => 'User',
            'gender' => 'male',
            'position' => '',
            'organization_name' => '',
            'address' => 'Address',
            'national_id' => 'RN' . rand(1000, 9999),
            'phone' => '2517' . rand(10000000, 99999999),
            'email' => 'respondent' . rand(10, 9999) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $caseNumber = 'CASE-' . rand(1000, 9999);
        $caseId = DB::table('court_cases')->insertGetId([
            'applicant_id' => $applicant->id,
            'case_number' => $caseNumber,
            'code' => null,
            'title' => 'Test case',
            'respondent_name' => $respondent->first_name,
            'respondent_address' => $respondent->address,
            'description' => 'Desc',
            'relief_requested' => null,
            'case_type_id' => $caseTypeId,
            'filing_date' => now()->toDateString(),
            'status' => 'pending',
            'assigned_user_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$admin, $caseId, $caseNumber, $applicant, $respondent];
    }
}
