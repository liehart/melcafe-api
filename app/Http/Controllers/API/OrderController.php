<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Faker\Provider\Base;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $userid = 1; #Auth::guard('api')->user()->id;
        $user_role = 'customer'; #Auth::guard('api')->user()->user_role;

        if ($userid == null || $user_role == null) {
            return $this->sendError('Unauthorized');
        }

        if ($user_role == 'admin') {
            $orders = Order::all();
        } else if ($user_role == 'customer') {
            $orders = Order::where('user_id', $userid)->get();
        } else {
            return $this->sendError('No permission to access this route');
        }

        if (count($orders) > 0) {
            return $this->sendResponse($orders, 'Retrieve all orders success');
        }

        return $this->sendError('No orders available');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $userid = 1; #Auth::guard('api')->user()->id;

        $request_data = $request->all();

        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'payment_method' => 'required|in:credit_card',
            'items.*.id' => 'required|numeric|exists:menus,id,deleted_at,NULL',
            'items.*.quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $items = $request_data['items'];
        $request_data['order_total'] = 0;
        $request_data['order_delivery'] = 0;
        $request_data['order_tax'] = 0;

        $request_data['user_id'] = $userid;
        $request_data['order_number'] = "test";
        $request_data['distance'] = 0;

        foreach ($items as $item) {
            $price = Menu::find($item['id'])->price;
            $request_data['order_total'] += $item['quantity'] * $price;
        }

        $request_data['order_grand_total'] =  ($request_data['order_total'] + $request_data['order_delivery']);
        $request_data['order_tax'] = $request_data['order_grand_total'] * 0.1;
        $request_data['order_grand_total'] += $request_data['order_tax'];

        $order = Order::create($request_data);

        foreach ($items as $item) {
            $item['menu_id'] = $item['id'];
            $item['order_id'] = $order['id'];
            OrderItem::create($item);
        }

        return $this->sendResponse($order, 'Order created');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found');
        }

        return $this->sendResponse($order, 'Order retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found');
        }

        $order->delete();
        return $this->sendResponse(null, 'Order deleted successfully.');
    }
}
