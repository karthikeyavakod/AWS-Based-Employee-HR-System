<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        if (Auth::attempt($this->credentials(), $this->boolean('remember'))) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials are invalid or the account is inactive.',
        ]);
    }

    /**
     * @return array{email: string, password: string, status: string}
     */
    private function credentials(): array
    {
        return [
            'email' => $this->string('email')->toString(),
            'password' => $this->string('password')->toString(),
            'status' => 'active',
        ];
    }
}
