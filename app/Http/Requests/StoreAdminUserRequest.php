<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['required', 'boolean'],
            'store_id' => [
                'nullable',
                'integer',
                'required_if:is_admin,0',
                Rule::exists('stores', 'id')->where('is_active', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ime i prezime su obavezni.',
            'email.required' => 'Email je obavezan.',
            'email.email' => 'Unesite ispravnu email adresu.',
            'email.unique' => 'Ovaj email je već zauzet.',
            'password.required' => 'Lozinka je obavezna.',
            'password.min' => 'Lozinka mora imati najmanje 8 karaktera.',
            'password.confirmed' => 'Potvrda lozinke se ne podudara.',
            'is_admin.required' => 'Izaberite ulogu korisnika.',
            'store_id.required_if' => 'Izaberite optiku za prodavca.',
            'store_id.exists' => 'Izabrana optika nije dostupna.',
        ];
    }
}
