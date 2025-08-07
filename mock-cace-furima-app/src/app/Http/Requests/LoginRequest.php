<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required | email',
            'password' => 'required | min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'メールアドレスは必須です。',
            'email.email'       => '有効なメールアドレスを入力してください。',
            'password.required' => 'パスワードは必須です。',
            'password.min'      => 'パスワードは8文字以上で入力してください。',
        ];
    }
}
