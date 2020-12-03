<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Validator;

class OrderStatusController extends BaseController
{
    public function store(Request $request)
    {
        $store_data = $request->all();
        $validator = Validator::make($store_data, [
            'order_id' => 'required|max:30',
            'order_status' => 'required|in:confirmed,on_process,on_delivery,completed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $menu = OrderStatus::create($store_data);

        return $this->sendResponse($menu, 'OrderStatus updated successfully');
    }
}
