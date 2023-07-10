<?php
namespace App\Components\Validation;

use App\Components\Api\JsonResponse;
use App\Components\Errors\ResourceMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest as HttpFormRequest;

class FormRequest extends HttpFormRequest {

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $errors = (new ValidationException($validator))->errors();
            throw new HttpResponseException(
                JsonResponse::send(true,ResourceMessage::resourceValidationMessage(),$errors,422)
            );
        }

        parent::failedValidation($validator);
    }
}


