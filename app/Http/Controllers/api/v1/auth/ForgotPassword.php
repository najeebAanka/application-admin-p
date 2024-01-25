<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\CPU\SMS_module;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPassword extends Controller
{
    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $verification_by = Helpers::get_business_settings('forgot_password_verification');
        DB::table('password_resets')->where('identity', 'like', "%{$request['identity']}%")->delete();

        if ($verification_by == 'email') {
            $customer = User::Where(['email' => $request['identity']])->first();
            if (isset($customer)) {
                $token = Str::random(120);
                DB::table('password_resets')->insert([
                    'identity' => $customer['email'],
                    'token' => $token,
                    'created_at' => now(),
                ]);
                $reset_url = 'https://mad_test.ae/reset?email=' . $customer['email'] . '&token=' . $token;
                Mail::to($customer['email'])->send(new \App\Mail\PasswordResetMail($reset_url));
                return Helpers::sendSuccess(translate('Email sent successfully.'), '');
            }
        } elseif ($verification_by == 'phone') {
            $customer = User::where('phone', 'like', "%{$request['identity']}%")->first();
            if (isset($customer)) {
                $token = rand(1000, 9999);
                DB::table('password_resets')->insert([
                    'identity' => $customer['phone'],
                    'token' => $token,
                    'created_at' => now(),
                ]);
                SMS_module::send($customer->phone, $token);
                return Helpers::sendSuccess(translate('otp sent successfully.'), '');
            }
        }
        return Helpers::sendError([['code' => 'not-found', 'message' => translate('user not found!')]], 404);
    }

    public function otp_verification_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return Helpers::sendSuccess(translate('otp verified.'), '');
        }

        $id = session('forgot_password_identity');
        $data = DB::table('password_resets')->where(['token' => $request['otp']])
            ->where('identity', 'like', "%{$id}%")
            ->first();

        if (isset($data)) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }


        return Helpers::sendError([['code' => 'not-found', 'message' => translate('invalid OTP')]], 404);
    }

    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required',
            'password' => 'required|same:confirm_password|min:8',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $data = DB::table('password_resets')
            ->where('identity', 'like', "%{$request['identity']}%")
            ->where(['token' => $request['otp']])->first();

        if (isset($data)) {
            DB::table('users')->where('email', 'like', "%{$data->identity}%")
                ->update([
                    'password' => bcrypt(str_replace(' ', '', $request['password']))
                ]);

            DB::table('password_resets')
                ->where('identity', 'like', "%{$request['identity']}%")
                ->where(['token' => $request['otp']])->delete();

            return Helpers::sendSuccess(translate('otp verified.'), '');
//            return Helpers::sendError([['code' => 'not-found', 'message' => translate('invalid OTP')]], 404);
        }

        return Helpers::sendError([['code' => 'invalid', 'message' => translate('Invalid token.')]], 501);
    }
}
