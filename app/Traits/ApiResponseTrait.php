<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function successResponse($data = [], $message = null, $code = 200)
    {
        $response = [
            'status' => 'ok',
        ];

        if ($message) {
            $response['message'] = $message;
        }

        $response = array_merge($response, $data);

        return response()->json($response, $code);
    }

    protected function errorResponse($errorCode, $message, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'error_code' => $errorCode,
            'message' => $message,
        ], $code);
    }
}
