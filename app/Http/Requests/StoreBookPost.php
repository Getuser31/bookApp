<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookPost extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'description' => 'required|max:5000',
            'date_of_publication' => 'date_format:d/m/Y',
            'author_id' => 'required|exists:authors,id',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id',
            'google_id' => 'nullable|max:255',
            'collection_id' => 'nullable|exists:collections,id',
            'picture' => 'nullable|file|image|max:5000',
        ];
    }
}
