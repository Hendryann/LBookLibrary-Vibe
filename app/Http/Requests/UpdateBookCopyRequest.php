<?php

namespace App\Http\Requests;

use App\Enums\CopyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateBookCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(CopyStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required.',
            'status.Illuminate\Validation\Rules\Enum' => 'The status must be one of: AVAILABLE, BORROWED, RESERVED, LOST.',
        ];
    }
}