<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function authorize()
    {
        // ログインユーザーのみ許可
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'body' => ['required','string','max:400'],
            'image' => ['nullable','file','mimes:jpeg,jpg,png',],
        ];
    }

    public function messages()
    {
        return [
            'body.required' => '本文を入力してください',
            'body.max' => '本文は400文字以内で入力してください',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}

