<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
    public function rules()
    {
        return [
            'chat' => 'required|max:400',
            'pict' => 'mimes:jpeg,png',
        ];
    }

    public function messages()
    {
        return [
            'chat.required' => '本文を入力してください',
            'chat.max' => '本文は400文字以内で入力してください',
            'pict.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
