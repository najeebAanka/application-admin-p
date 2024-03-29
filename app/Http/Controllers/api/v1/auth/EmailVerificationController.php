<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\CPU\SMS_module;
use App\Http\Controllers\Controller;
use App\Model\PhoneOrEmailVerification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;

class EmailVerificationController extends Controller
{
    public function check_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'temporary_token' => 'required',
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        if(User::where('email', $request->email)->first()->temporary_token != $request->temporary_token) {
            return Helpers::sendSuccess(translate('temporary_token_mismatch'),'');
        }

        $token = rand(1000, 9999);
        DB::table('phone_or_email_verifications')->insert([
            'phone_or_email' => $request['email'],
            'token' => $token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            Mail::to($request['email'])->send(new \App\Mail\EmailVerification($token));
            $response = translate('check_your_email');
        } catch (\Exception $exception) {
            $response= translate('email_failed');
        }
        return Helpers::sendSuccess($response,[
            'token' => 'active'
        ]);
    }

    public function verify_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'temporary_token' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $verify = PhoneOrEmailVerification::where(['phone_or_email' => $request['email'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            try {
                $user = User::where(['temporary_token' => $request['temporary_token']])->first();
                $user->email = $request['email'];
                $user->is_email_verified = 1;
                $user->save();
                $verify->delete();
            } catch (\Exception $exception) {
                return Helpers::sendSuccess(translate('temporary_token_mismatch'),'');
            }

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return Helpers::sendSuccess(translate('otp_verified'), [
                'token' => $token
            ]);
        }


        return Helpers::sendError([['message' => translate('invalid_token')]], 501);
    }
}
