<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a note title.',
            'title.max' => 'The note title may not be greater than 150 characters.',
            'content.required' => 'Please write something in your note.',
            'content.max' => 'The note content may not be greater than 10,000 characters.',
        ];
    }
}
