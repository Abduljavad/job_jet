<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
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
            'job_title' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:locations,id',
            'content' => 'nullable|string',
            'email' => 'nullable|string|email',
            'phone' => 'nullable|string',
            'image.url' => 'nullable|string|url',
            'image.alt_text' => 'nullable|string|max:255',
            'experience_required' => 'nullable|string|max:255',
            'salary' => 'nullable|string|max:255',
            'joining_time' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'categories' => 'array|exists:categories,id',
            'status' => 'string|in:opened,closed'
        ];
    }
}
