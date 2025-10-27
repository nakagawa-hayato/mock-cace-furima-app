<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'brand' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:255'],
            'condition_id' => ['required'],
            'image' => ['required','mimes:jpeg,png,jpg'],
            'category' => ['required','array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください。',
            'price.required' => '価格を入力してください。',
            'price.integer' => '価格は数値で入力してください。',
            'price.min' => '価格は0円以上である必要があります。',
            'description.required' => '商品説明を入力してください。',
            'description.max' => '商品説明は255文字以内で入力してください。',
            'condition_id.required' => '商品の状態を選択してください。',
            'image.required' => '画像をアップロードしてください。',
            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
            'category.required' => 'カテゴリを選択してください。。',
        ];
    }
}
