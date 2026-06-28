<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role?->value ?? '';

        return in_array($role, ['admin', 'librarian'], true);
    }

    public function rules(): array
    {
        $categoryId = $this->route('category') instanceof \App\Models\Category
            ? $this->route('category')->id
            : $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255', "unique:categories,name,{$categoryId}"],
        ];
    }
}
