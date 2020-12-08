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

        $lat = $data['lat'];
        $lon = $data['lng'];

        return $this->sendResponse($this->getDistance($lat, $lon), "Success", 201);
    }

    public function getDistance($lat, $lon) {
        $apiKey = 'AIzaSyAw1JxXEUrHvax8cItD7cxl1pO3Ogjhgeo';

        $latFrom = -7.7794195;
        $lonFrom = 110.416129;

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $latFrom . "," . $lonFrom . "&destinations=" . $lat . "," . $lon . "&mode=driving&language=id-ID&key=" . $apiKey;

        $response = Http::get($url);

        return json_decode($response->body());
    }
}
