<?php

namespace App\Http\Requests;


class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|max:255|email|unique:users',
            'phone_number' => 'required|unique:users',
            'password' => 'required',
        ];
    }

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
     * @return array
     * Custom validation message
     */
    public function messages()
    {
        return [
            'phone_number.required' => 'Telefon numarası girilmesi zorunlu !',
            'phone_number.unique' => 'Telefon numarası zaten kayıtlı !',
            'email.required' => 'E-mail alanı girilmesi zorunlu !',
            'email.unique' => 'E-mail zaten kayıtlı !',
            'password.required' => 'Parola alanı girilmesi zorunlu !',
        ];
    }
}
