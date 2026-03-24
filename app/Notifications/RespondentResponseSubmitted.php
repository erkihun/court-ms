<?php

namespace App\Notifications;

use App\Models\RespondentResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RespondentResponseSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private RespondentResponse $response)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'respondent_response_submitted',
            'response_id' => $this->response->id,
            'case_number' => $this->response->case_number,
            'response_number' => $this->response->response_number,
            'title' => $this->response->title,
            'message' => __('notifications.respondent_response_submitted', [
                'case' => $this->response->case_number ?? __('notifications.case_unknown'),
                'title' => $this->response->title ?? '',
            ]),
        ];
    }
}
