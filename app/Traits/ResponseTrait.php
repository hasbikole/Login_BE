<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ResponseTrait
{
    /**
     * Generate success type response.
     *
     * Returns the success data and message if there is any error
     *
     * @param object $data
     * @param string $message
     * @param integer $status_code
     * @return JsonResponse
     */
    public function responseSuccess($data, $logVariable, $message = "Successful", $status_code = JsonResponse::HTTP_OK): JsonResponse
    {

        Log::channel("single")->info($logVariable['message'], $logVariable);

        return response()->json([
            'status'  => $status_code,
            'message' => $message,
            'errors'  => null,
            'data'    => $data,
        ], $status_code);
    }

    public function responseSuccessWithOutLog($data,  $message = "Successful", $status_code = JsonResponse::HTTP_OK): JsonResponse
    {

        return response()->json([
            'status'  => $status_code,
            'message' => $message,
            'errors'  => null,
            'data'    => $data,
        ], $status_code);
    }

    /**
     * Generate Error response.
     *
     * Returns the errors data if there is any error
     *
     * @param object $errors
     * @return JsonResponse
     */
    public function responseError($errors, $logVariable, $message = 'Data is invalid', $status_code = JsonResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        // Log::channel("single")->error($logVariable['message'], $logVariable);

        return response()->json([
            'status'  => $status_code,
            'message' => $message,
            'errors'  => $errors,
            'data'    => $errors,
        ], $status_code);
    }

    public function responseErrorWithOutLog($errors, $message = 'Data is invalid', $status_code = JsonResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        if($message == 'Data is invalid'){
            $message = $errors;
        }
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
            'data'    => null,
        ], $status_code);
    }

    
}
