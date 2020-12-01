<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use function GuzzleHttp\Psr7\str;

class AuthController extends BaseController
{

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        $success['token'] = $user->createToken('melcafe')->accessToken;
        $success['user'] = $user;

        Verification::create(['user_id' => $user->id, 'token' => Str::random(30)]);

        return $this->sendResponse($success, 'User register success');
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
