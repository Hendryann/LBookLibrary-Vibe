<?php

namespace App\Http\Requests;

use App\Enums\CopyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreBookCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled via middleware
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', new Enum(CopyStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.Illuminate\Validation\Rules\Enum' => 'The status must be one of: AVAILABLE, BORROWED, RESERVED, LOST.',
        ];
    }
}