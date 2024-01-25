<?php

namespace App\Http\Controllers\api\v2\delivery_man\auth;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $d_man = DeliveryMan::where(['email' => $request['email']])->first();
        if (isset($d_man) && $d_man['is_active'] == 1 && Hash::check($request->password, $d_man->password)) {
            $token = Str::random(50);
            $d_man->auth_token = $token;
            $d_man->save();
            Helpers::sendSuccess(translate('Data Got!'),['token' => $token]);
        } else {
            return Helpers::sendError([['code' => 'auth-001', 'message' => translate('Invalid credential or account suspended')]], 501);

        }
    }
}
