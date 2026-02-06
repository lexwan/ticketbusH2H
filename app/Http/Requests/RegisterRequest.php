<?php

namespace App\Http\Requests;

class RegisterRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'mitra_id' => 'nullable|exists:mitra,id'
        ];
    }
}
