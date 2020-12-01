<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Faker\Provider\Base;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }
}
