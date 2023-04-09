<?php

namespace App\Http\Requests;

use App\Traits\ResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

abstract class FormRequest extends LaravelFormRequest
{
    /**
     * Response trait to handle return responses.
     */
    use ResponseTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // Register message kısmını bozduğu için kaldırıldı.
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->getMessage();
        $errors = collect(explode("(", $errors))[0];
        $logVariable = [
            'message' => $errors . " status_code: " . 422
        ];
        throw new HttpResponseException(
            $this->responseError($errors, $logVariable, $errors, 422)
        );
    }
}
