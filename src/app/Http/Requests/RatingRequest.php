<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'item_id' => ['required','integer','exists:items,id'],
            'rated_user_id' => ['required','integer','exists:users,id'],
            'score' => ['required','integer','between:1,5'],
        ];
    }

    public function messages()
    {
        return [
            'score.between' => '評価は1から5の間で指定してください',
        ];
    }
}
