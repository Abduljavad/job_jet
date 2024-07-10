<?php

namespace App\Http\Requests;

use App\Http\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
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
            'plan' => 'nullable|max:255',
            'interval' => 'nullable|string|max:255',
            'interval_count' => 'nullable',
            'description' => 'nullable',
            'price' => 'nullable|numeric',
            'is_trial' => 'nullable|boolean',
            'currency' => ['nullable', Rule::enum(Currency::class)],
        ];
    }
}
