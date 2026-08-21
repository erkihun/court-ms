<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\CourtCase;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDecisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'case_id' => [
                'required',
                Rule::exists(CourtCase::class, 'id')
                    ->where('status', 'closed'),
            ],
            'case_file_number' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'decision_content' => ['required', 'string'],
            'decision_date' => ['required', 'date'],
            'reviewing_admin_user_names' => ['nullable', 'array'],
            'reviewing_admin_user_names.*' => ['string', 'max:255'],
            'judges_comments' => ['nullable', 'string', 'max:5000'],
            'judges' => ['nullable', 'array', 'size:3'],
            'judges.*.admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'case_id.exists' => __('decisions.validation.closed_case_required'),
        ];
    }
}
