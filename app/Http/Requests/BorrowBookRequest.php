<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BorrowBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'copy_id' => ['required', 'integer', 'exists:book_copies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'copy_id.required' => 'A book copy must be specified to borrow.',
            'copy_id.exists' => 'The selected book copy does not exist.',
        ];
    }
}