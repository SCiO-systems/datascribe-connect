<?php

namespace App\Http\Requests\Vocabularies;

use Illuminate\Foundation\Http\FormRequest;

class ShowVocabularyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $isVocabularyViewer = $this->vocabulary->users()
            ->where('user_id', $this->user()->id)
            ->exists();

        return $isVocabularyViewer;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
