<?php

namespace App\Http\Requests\Blocks;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class PublishBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $isAdmin = $this->user()->role === User::SYSTEM_ROLE_ADMIN;

        return $isAdmin;
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
