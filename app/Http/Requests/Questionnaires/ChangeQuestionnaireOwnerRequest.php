<?php

namespace App\Http\Requests\Questionnaires;

use Illuminate\Foundation\Http\FormRequest;

class ChangeQuestionnaireOwnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $isQuestionnaireOwner = $this->questionnaire->owner->id === $this->user()->id;

        return $isQuestionnaireOwner;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ['owner_id' => 'required|exists:users,id'];
    }
}
