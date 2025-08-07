<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'name'     => 'required | string | max:255',
            'email'    => 'required | string | email | max:255 |unique:users',
            'password' => 'required | string | min:8 | confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => '名前は必須です。',
            'email.required'    => 'メールアドレスは必須です。',
            'email.email'       => '有効なメールアドレスを入力してください。',
            'email.unique'      => 'そのメールアドレスはすでに登録されています。',
            'password.required' => 'パスワードは必須です。',
            'password.min'      => 'パスワードは8文字以上で入力してください。',
            'password.confirmed'=> 'パスワード確認が一致しません。',
        ];
    }
}
