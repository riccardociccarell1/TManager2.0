<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['prohibited'], // Prevent users from setting their own role during registration
        ];
    }


    /**
     * Get the custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
                // Name errors.
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name cannot contain more than 255 characters.',

            // Email errors.
            'email.required' => 'The email is required.',
            'email.email' => 'The email format is not valid.',
            'email.max' => 'The email cannot contain more than 255 characters.',
            'email.unique' => 'This email is already registered.',

            // Password errors.
            'password.required' => 'The password is required.',
            'password.confirmed' => 'The password confirmation does not match.',

            // Role error.
            'role.prohibited' => 'The role cannot be selected during registration.',
        ];
    }
}
