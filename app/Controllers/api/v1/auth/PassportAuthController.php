<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Seller;
use App\Model\Shop;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;
use App\CPU\ImageManager;

class PassportAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type' => 'required|in:1,2',
        ]);

        Log::info($request->all());

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }
        $temporary_token = Str::random(40);

        if ($request->user_type == 1) {

            $validator = Validator::make($request->all(), [
                'f_name' => 'required',
                'l_name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required|min:8'
            ], [
                'f_name.required' => 'The first name field is required.',
                'l_name.required' => 'The last name field is required.',
            ]);

            if ($validator->fails()) {
                return Helpers::sendError(Helpers::error_processor($validator), 403);
            }

            $user = User::create([
                'name' => $request->f_name . ' ' . $request->l_name,
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'email' => $request->email,
                'is_active' => 1,
                'password' => bcrypt($request->password),
                'temporary_token' => $temporary_token,
            ]);

            $phone_verification = Helpers::get_business_settings('phone_verification');
            $email_verification = Helpers::get_business_settings('email_verification');
            if ($phone_verification && !$user->is_phone_verified) {
                return Helpers::sendSuccess('Data Got!', ['temporary_token' => $temporary_token]);
            }
            if ($email_verification && !$user->is_email_verified) {
                return Helpers::sendSuccess('Data Got!', ['temporary_token' => $temporary_token]);
            }

            $token = $user->createToken('LaravelAuthApp')->accessToken;

            return Helpers::sendSuccess('Data Got!', [
                'token' => $token,
                'user' => \App\Resources\User::make($user)
            ]);
        } else {

            $validator = Validator::make($request->all(), [
                'f_name' => 'required',
                'l_name' => 'required',
                'email' => 'required|unique:sellers',
                'password' => 'required|min:8',
                'commercial_name' => 'required',
                'location' => 'required',
            ], [
                'f_name.required' => 'The first name field is required.',
                'l_name.required' => 'The last name field is required.',
            ]);

            if ($validator->fails()) {
                return Helpers::sendError(Helpers::error_processor($validator), 403);
            }


            $image = '';
            $image_shop = '';
            $banner = '';

            if ($request->hasFile('image')) {
                $image = ImageManager::upload('seller/', 'png', $request->file('image'));
                $image_shop = ImageManager::upload('shop/', 'png', $request->file('image'));
            }

            if ($request->hasFile('banner')) {
                $banner = ImageManager::upload('shop/banner/', 'png', $request->file('banner'));
            }

            $seller = Seller::create([
                'name' => $request->f_name . ' ' . $request->l_name,
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'email' => $request->email,
                'status' => 1,
                'image' => $image,
                'password' => bcrypt($request->password),
                'temporary_token' => $temporary_token
            ]);


            $shop = new Shop();
            $shop->seller_id = $seller->id;
            $shop->name = $request->commercial_name;
            $shop->address = $request->location;
            $shop->image = $image_shop;
            $shop->banner = $banner;
            $shop->save();

            return Helpers::sendSuccess('Data Got!', [
                'token' => md5($seller->id . ' ' . $seller->name . ' ' . $seller->email),
                'user' => \App\Resources\Seller::make($seller)
            ]);
        }


    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $user_id = $request['email'];
        if (filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
            $medium = 'email';
        } else {
            $count = strlen(preg_replace("/[^\d]/", "", $user_id));
            if ($count >= 9 && $count <= 15) {
                $medium = 'phone';
            } else {
                return Helpers::sendError([['code' => 'email', 'message' => 'Invalid email address or phone number']], 501);
            }
        }

        $data = [
            $medium => $user_id,
            'password' => $request->password
        ];

        $user = User::where([$medium => $user_id])->first();

        if (isset($user) && $user->is_active && auth()->attempt($data)) {
            $user->temporary_token = Str::random(40);
            $user->save();

            $phone_verification = Helpers::get_business_settings('phone_verification');
            $email_verification = Helpers::get_business_settings('email_verification');
            if ($phone_verification && !$user->is_phone_verified) {
                return Helpers::sendSuccess('Data Got!', ['temporary_token' => $user->temporary_token]);
            }
            if ($email_verification && !$user->is_email_verified) {
                return Helpers::sendSuccess('Data Got!', ['temporary_token' => $user->temporary_token]);
            }


            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return Helpers::sendSuccess('Data Got!', [
                'token' => $token,
                'user' => \App\Resources\User::make($user)
            ]);
        } else {
            return Helpers::sendError([['code' => 'auth-001', 'message' => translate('Customer_not_found_or_Account_has_been_suspended')]], 501);
        }
    }


    public function logout(Request $request)
    {

        if (auth('api')->user() != null) {
            auth('api')->user()->AauthAcessToken()->delete();
        }

        return Helpers::sendSuccess('Logged out!', '');
    }
}
