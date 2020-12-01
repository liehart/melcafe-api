<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Validator;
use function GuzzleHttp\Psr7\str;
use function Symfony\Component\String\u;

class AuthController extends BaseController
{
    const _ADMIN = 'admin';
    const _DRIVER = 'driver';
    const _CUSTOMER = 'customer';

    public static $role = [self::_ADMIN, self::_CUSTOMER, self::_DRIVER];

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required',
            'address' => 'required',
            'gender' => 'required|in:male,female,other',
            'telephone' => 'required|numeric',
            'dob' => 'required|date_format:Y-m-d|before:today'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        $input['user_id'] = $user->id;
        $input['token'] = Str::random(30);

        Verification::create($input);
        Customer::create($input);

        return $this->sendResponse($user, 'User register success');
    }

    public function login(Request $request) {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $success['token'] = $user->createToken('melcafe')->accessToken;
            $success['user'] = $user;

            return $this->sendResponse($success, 'User login success');
        }

        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }
}
