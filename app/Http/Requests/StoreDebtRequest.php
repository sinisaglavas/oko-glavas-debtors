<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'debtor_id' => ['required', 'integer', 'exists:debtors,id'],
            'incurred_on' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'string', 'regex:/^\d+(?:[.,]\d{1,2})?$/'],
            'installment_count' => ['required', 'integer', 'min:2', 'max:24'],
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
