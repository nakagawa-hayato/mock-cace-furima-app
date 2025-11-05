<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        // Authorization はコントローラ側で厳密にチェックする想定だが、
        // ここではログインユーザなら true を返す
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'body' => ['required','string','max:400'],
        ];
    }

    public function messages()
    {
        return [
            'body.required' => '本文を入力してください',
            'body.max' => '本文は400文字以内で入力してください',
        ];
    }
}
