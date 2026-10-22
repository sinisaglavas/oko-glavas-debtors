<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'debt_id' => ['required', 'integer', 'exists:debts,id'],
            'amount' => ['required', 'string', 'regex:/^\d+(?:[.,]\d{1,2})?$/'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:1000'],
            'submission_token' => ['required', 'string', 'max:100'],
        ];
    }

    public function amountMinor(): int
    {
        try {
            return Money::toMinor($this->string('amount')->toString());
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['amount' => $exception->getMessage()]);
        }
    }
}
