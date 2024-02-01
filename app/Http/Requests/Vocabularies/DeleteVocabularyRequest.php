<?php

namespace App\Http\Requests\Vocabularies;

use Illuminate\Foundation\Http\FormRequest;

class DeleteVocabularyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $isVocabularyOwner = $this->vocabulary->owner->id === $this->user()->id;

        return $isVocabularyOwner;
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
