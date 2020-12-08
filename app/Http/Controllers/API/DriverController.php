<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class DriverController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $driver = Driver::with('user')->get();

        if (count($driver) > 0)
            return $this->sendResponse($driver, 'Driver retrieved successfully');

        return $this->sendError('Driver empty');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //'user_id', 'distance', 'income', 'current_order_id'

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|max:100',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['distance'] = 0;
        $input['income'] = 0;
        $input['user_role'] = "driver";

        $user = User::create($input);

        $input['user_id'] = $user->id;

        $menu = Driver::create($input);
        $menu['user'] = $menu->user;

        return $this->sendResponse($menu, 'Driver created successfully');
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
        $driver = Driver::find($id);

        if (is_null($driver))
            return $this->sendError('Driver not found');

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|max:100',
            'distance' => 'required|numeric',
            'income' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $driver->distance = $input['distance'];
        $driver->income = $input['income'];
        $driver->user->name = $input['name'];
        $driver->user->save();
        $driver->save();

        return $this->sendResponse($driver, 'Driver created successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $driver = Driver::find($id);

        if (is_null($driver))
            return $this->sendError('Driver not found');

        $driver->delete();

        return $this->sendResponse(null, 'Driver deleted successfully.');
    }
}
