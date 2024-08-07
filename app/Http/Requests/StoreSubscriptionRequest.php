<?php

namespace App\Http\Requests;

use App\Http\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
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
            'plan' => 'required|string|max:255',
            'interval' => 'required|string',
            'interval_count' => 'required|integer',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'is_trial' => 'nullable|boolean',
            'currency' => ['required', Rule::enum(Currency::class)],
        ];
    }
}
