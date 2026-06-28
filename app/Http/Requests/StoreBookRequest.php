<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role?->value ?? '';

        return in_array($role, ['admin', 'librarian'], true);
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'isbn'             => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:' . ((int) date('Y') + 1)],
            'author_id'        => ['required', 'integer', 'exists:authors,id'],
            'category_ids'     => ['nullable', 'array'],
            'category_ids.*'   => ['integer', 'exists:categories,id'],
        ];
    }
}
