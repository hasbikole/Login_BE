<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UploadRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'multiFiles' =>'required|mimes:jpeg,png,jpg|max:5120'
        ];
    }

    public function messages()
    {
        return [
            'multiFiles.required' => 'Resim alanı girilmesi zorunlu !',
            'multiFiles.image' => 'Resim olmalı ! ',
            'multiFiles.mimes' => 'Sadece jpeg png ve jpg desteklemektedir',
            'multiFiles.max' => '5Mb dan büyük dosya yükleyemezsiniz',
        ];
    }
}
