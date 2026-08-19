<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
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
            // Title errors.
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title cannot contain more than 255 characters.',    

            // Description errors.
            'description.string' => 'The description must be a valid string.',

            // Status errors.
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of the following: pending, in_progress, completed.',

            // Priority errors.
            'priority.in' => 'The priority must be one of the following: low, medium, high.',

            // Due date errors.
            'due_date.date' => 'The due date must be a valid date.',
        ];
    }
        
}
