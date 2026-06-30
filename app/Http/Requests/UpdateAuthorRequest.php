<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        return in_array($user->role, [\App\Enums\Role::ADMIN, \App\Enums\Role::LIBRARIAN], true);
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'biography' => ['nullable', 'string'],
        ];
    }
}
