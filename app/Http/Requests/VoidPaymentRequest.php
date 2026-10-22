<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'debt_id' => ['required', 'integer', 'exists:debts,id'],
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
