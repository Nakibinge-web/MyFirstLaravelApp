<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'target_date' => ['required', 'date', 'after:today'],
            'description' => ['nullable', 'string', 'max:500'],
        ];

        // Only validate current_amount on update
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['current_amount'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Goal name is required.',
            'target_amount.required' => 'Please enter a target amount.',
            'target_amount.min' => 'Target amount must be greater than 0.',
            'target_date.required' => 'Please select a target date.',
            'target_date.after' => 'Target date must be in the future.',
        ];
    }
}
