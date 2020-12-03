<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function sendResponse($result, $message, $resp_code = 200) {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];

        return response()->json($response, $resp_code);
    }

    public function sendError($error, $errorMessage = [], $errorCode = 404) {
        $response = [
            'success' => true,
            'message' => $error,
        ];

        if (!empty($errorMessage)) {
            $response['data'] = $errorMessage;
        }

        return response()->json($response, $errorCode);
    }
}
