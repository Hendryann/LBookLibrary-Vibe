<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $role = auth()->user()->role?->value ?? '';
        return in_array($role, ['admin', 'librarian'], true);
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:255', "unique:categories,name,{$categoryId}"],
        ];
    }
}
