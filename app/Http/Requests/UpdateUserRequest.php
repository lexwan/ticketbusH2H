<?php

namespace App\Http\Requests;

class UpdateUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:admin,mitra',
            'mitra_id' => 'nullable|exists:mitra,id',
            'status' => 'sometimes|in:active,inactive'
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role must be admin or mitra',
            'mitra_id.exists' => 'Mitra not found'
        ];
    }
}
