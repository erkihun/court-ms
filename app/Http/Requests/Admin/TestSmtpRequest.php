<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class TestSmtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('settings.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'email:rfc', 'max:255'],
        ];
    }
}
