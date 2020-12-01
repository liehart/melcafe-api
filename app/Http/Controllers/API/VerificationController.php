<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Verification;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Validator;
use function Composer\Autoload\includeFile;

class VerificationController extends BaseController
{
    public function verify(Request $request) {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors());
        }

        $verification = Verification::where('token', $request->all()['token'])->first();

        if (!$verification) {
            return $this->sendError('Token not valid');
        }

        if ($verification->is_used) {
            return $this->sendError('Token has been used');
        }

        $verification->is_used = true;
        $verification->save();

        User::find($verification->user_id)->markEmailAsVerified();

        return $this->sendResponse(null, 'User verification success, please login to continue.');

    }
}
