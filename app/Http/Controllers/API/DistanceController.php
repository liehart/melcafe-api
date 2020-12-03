<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Validator;

class DistanceController extends BaseController
{
    public function index(Request $request) {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }
        $apiKey = 'AIzaSyAw1JxXEUrHvax8cItD7cxl1pO3Ogjhgeo';

        $latFrom = -7.7794195;
        $lonFrom = 110.416129;

        $latTo = $data['lat'];
        $lonTo = $data['lng'];

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $latFrom . "," . $lonFrom . "&destinations=" . $latTo . "," . $lonTo . "&mode=driving&language=id-ID&key=" . $apiKey;

        $response = Http::get($url);

        $answer = json_decode($response->body());

        return $this->sendResponse($answer, "Success", 201);

    }
}
