<?php

namespace App\Traits;

trait ApiResponse
{
    public function successResponse($data = null, $message = 'OK', $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
