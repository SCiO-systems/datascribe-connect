<?php

namespace App\Http\Requests\Vocabularies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateVocabularyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'listname' => Rule::unique('vocabularies', 'listname')
                ->where('created_by_id', Auth::id())
                ->ignore('id')
        ];
    }
}
