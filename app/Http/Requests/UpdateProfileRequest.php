<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $this->user()->id],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already taken.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'address.max' => 'Address must not exceed 500 characters.',
            'date_of_birth.date' => 'Please provide a valid date.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'avatar.image' => 'Avatar must be an image file.',
            'avatar.mimes' => 'Avatar must be a jpeg, png, jpg, or gif file.',
            'avatar.max' => 'Avatar file size must not exceed 2MB.'
        ];
    }
}
