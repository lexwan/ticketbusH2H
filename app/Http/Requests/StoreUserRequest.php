<?php

namespace App\Http\Requests;

class StoreUserRequest extends BaseRequest
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
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,mitra',
            'mitra_id' => 'nullable|exists:mitra,id',
            'status' => 'nullable|in:active,inactive'
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role is required',
            'role.in' => 'Role must be admin or mitra',
            'mitra_id.exists' => 'Mitra not found'
        ];
    }
}
