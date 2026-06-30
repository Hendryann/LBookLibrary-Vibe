<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtendTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'extra_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function extraDays(): int
    {
        return (int) $this->input('extra_days', \App\Services\TransactionService::EXTENSION_DAYS);
    }
}