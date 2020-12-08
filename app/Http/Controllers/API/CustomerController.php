<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Cassandra\Custom;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $customer = Customer::with('user')->get();

        if (count($customer) > 0)
            return $this->sendResponse($customer, 'Customer retrieved successfully');

        return $this->sendError('Customer empty');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $storeData = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'address' => 'required',
            'gender' => 'required|in:male,female,other',
            'telephone' => 'required|numeric',
            'dob' => 'required|date_format:Y-m-d|before:today'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $user = User::find($id);

        $user->name = $storeData['name'];
        $user->customer->address = $storeData['address'];
        $user->customer->gender = $storeData['gender'];
        $user->customer->telephone = $storeData['telephone'];
        $user->customer->dob = $storeData['dob'];
        $user->customer->save();
        $user->save();

        return $this->sendResponse($user, 'User register success', 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (is_null($customer))
            return $this->sendError('Customer not found');

        $customer->user->delete();

        return $this->sendResponse(null, 'Customer deleted successfully.');
    }

    public function updateImage(Request $request, $id) {
        $customer = Customer::find($id);

        if (is_null($customer))
            return $this->sendError('Menu not found');

        $store_data = $request->all();
        $validator = Validator::make($store_data, [
            'image' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation error', $validator->errors());

        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $extension = $request->image->extension();
                $name = $_SERVER['REQUEST_TIME'];
                $request->image->storeAs('/public', $name.".".$extension);
                $customer->image = Storage::url($name.".".$extension);
                $customer->save();
                return $this->sendResponse($customer->image, 'Updage image success');
            }
        }

        return $this->sendError('Update image failed');
    }
}
