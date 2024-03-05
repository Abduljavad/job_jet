<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'full_name' => 'required|max:255|string',
            'industry' => 'required|string|max:255',
            'total_experience' => 'required|string|max:255',
            'job_category' => 'required|string|max:255',
            'current_job_position' => 'required|string|max:255',
            'resume' => 'required',
        ];
    }
}
