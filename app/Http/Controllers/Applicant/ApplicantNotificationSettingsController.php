<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantNotificationSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $aid = auth('applicant')->id();

        // Ensure a row exists (idempotent)
        $settings = DB::table('applicant_notification_settings')
            ->where('applicant_id', $aid)
            ->first();

        if (!$settings) {
            DB::table('applicant_notification_settings')->insert([
                'applicant_id' => $aid,
                'hearing_email' => true,
                'message_email' => true,
                'status_email'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $settings = DB::table('applicant_notification_settings')
                ->where('applicant_id', $aid)->first();
        }

        return view('applicant.notifications.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $aid = auth('applicant')->id();

        $data = $request->validate([
            'hearing_email' => ['nullable', 'boolean'],
            'message_email' => ['nullable', 'boolean'],
            'status_email'  => ['nullable', 'boolean'],
        ]);

        // Checkboxes send only when checked; coerce to bool
        $payload = [
            'hearing_email' => (bool)$request->boolean('hearing_email'),
            'message_email' => (bool)$request->boolean('message_email'),
            'status_email'  => (bool)$request->boolean('status_email'),
            'updated_at'    => now(),
        ];

        DB::table('applicant_notification_settings')
            ->updateOrInsert(
                ['applicant_id' => $aid],
                array_merge($payload, ['created_at' => now()])
            );

        return back()->with('success', 'Notification preferences updated.');
    }
}
