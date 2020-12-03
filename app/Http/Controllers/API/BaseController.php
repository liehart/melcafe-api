<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /*
     * BaseController adalah kelas yang menghandle response API
     * terdapat dua jenis response API
     * Pertama adalah respon success yang di tangani oleh fungsi
     * sendResponse()
     * Fungsi ini memiliki parameter $result sebagai data yang akan dilampirkan
     * $message sebagai pesan petunjuk dari API
     * $resp_code atau response code secara optional
     * karena resp_code tidak hanya ada 200 maka diperlukan kadang kala
     */

    public function sendResponse($result, $message, $resp_code = 200) {

        /*
         * Data akan disusun dalam PHP array
         */

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];

        /*
         * Setelah itu data dikirim sebagai response API dalam bentuk JSON
         */

        return response()->json($response, $resp_code);
    }

    /*
     * sendError adalah fungsi untuk mengirim response error
     * $error adalah pesan error yang ingin dikirim oleh API
     * $errorMessage default array kosong adalah pesan tambahan
     * yang ingin dikirim oleh API (optional)
     * $errorCode secara default adalah 404 namun dapat diubah
     * sesuai kebutuhan
     */

    public function sendError($error, $errorMessage = [], $errorCode = 404) {

        /*
         * Data akan disusun dalam PHP array
         */

        $response = [
            'success' => true,
            'message' => $error,
        ];

        /*
         * Jika parameter $errorMessage diisi (tidak kosong)
         * maka akan di set sebagai data dalam json selain info success
         * dan message error.
         */

        if (!empty($errorMessage)) {
            $response['data'] = $errorMessage;
        }

        /*
         * Setelah itu data dikirim sebagai response API dalam bentuk JSON
         */

        return response()->json($response, $errorCode);
    }
}
