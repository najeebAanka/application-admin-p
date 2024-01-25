<?php

namespace App\Http\Controllers\api\v2\seller;

use App\CPU\BackEndHelper;
use App\CPU\Convert;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\DeliveryMan;
use App\Model\OrderTransaction;
use App\Model\Product;
use App\Model\Review;
use App\Model\Seller;
use App\Model\SellerWallet;
use App\Model\Shop;
use App\Model\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function App\CPU\translate;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    public function shop_info(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
            $product_ids = Product::where(['user_id' => $seller['id'], 'added_by' => 'seller'])->pluck('id')->toArray();
            $shop = Shop::where(['seller_id' => $seller['id']])->first();
            $shop['rating'] = round(Review::whereIn('product_id', $product_ids)->avg('rating'), 3);
            $shop['rating_count'] = Review::whereIn('product_id', $product_ids)->count();
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        return Helpers::sendSuccess("Data Got!", $shop);
    }

    public function seller_delivery_man(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);
        if ($data['success'] == 1) {
            $seller = $data['data'];
            $delivery_men = DeliveryMan::where(['seller_id' => $seller['id']])->get();
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        return Helpers::sendSuccess("Data Got!", $delivery_men);
    }

    public function shop_product_reviews(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
            $product_ids = Product::where(['user_id' => $seller['id'], 'added_by' => 'seller'])->pluck('id')->toArray();
            $reviews = Review::whereIn('product_id', $product_ids)->with(['product', 'customer'])->get();
            $reviews->map(function ($data) {
                $data['attachment'] = json_decode($data['attachment'], true);
                return $data;
            });
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        return Helpers::sendSuccess("Data Got!", $reviews);
    }

    public function seller_info(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        return Helpers::sendSuccess(translate('Data Got!'), Seller::with(['wallet'])->withCount(['product', 'orders'])->where(['id' => $seller['id']])->first());
    }

    public function shop_info_update(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        $old_image = Shop::where(['seller_id' => $seller['id']])->first()->image;
        $image = $request->file('image');
        if ($image != null) {
            $imageName = ImageManager::update('shop/', $old_image, 'png', $request->file('image'));
        } else {
            $imageName = $old_image;
        }

        Shop::where(['seller_id' => $seller['id']])->update([
            'name' => $request['name'],
            'address' => $request['address'],
            'contact' => $request['contact'],
            'image' => $imageName,
            'updated_at' => now()
        ]);

        return Helpers::sendSuccess(translate('Shop info updated successfully!'), '');
    }

    public function seller_info_update(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        $old_image = Seller::where(['id' => $seller['id']])->first()->image;
        $image = $request->file('image');
        if ($image != null) {
            $imageName = ImageManager::update('seller/', $old_image, 'png', $request->file('image'));
        } else {
            $imageName = $old_image;
        }

        Seller::where(['id' => $seller['id']])->update([
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'bank_name' => $request['bank_name'],
            'branch' => $request['branch'],
            'account_no' => $request['account_no'],
            'holder_name' => $request['holder_name'],
            'password' => $request['password'] != null ? bcrypt($request['password']) : Seller::where(['id' => $seller['id']])->first()->password,
            'image' => $imageName,
            'updated_at' => now()
        ]);

        if ($request['password'] != null) {
            Seller::where(['id' => $seller['id']])->update([
                'auth_token' => Str::random('50')
            ]);
        }

        return Helpers::sendSuccess(translate('Info updated successfully!'), '');
    }

    public function withdraw_request(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);
        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);;
        }

        $wallet = SellerWallet::where('seller_id', $seller['id'])->first();
        if (($wallet->total_earning) >= Convert::usd($request['amount']) && $request['amount'] > 1) {
            DB::table('withdraw_requests')->insert([
                'seller_id' => $seller['id'],
                'amount' => Convert::usd($request['amount']),
                'transaction_note' => null,
                'approved' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $wallet->total_earning -= BackEndHelper::currency_to_aed($request['amount']);
            $wallet->pending_withdraw += BackEndHelper::currency_to_aed($request['amount']);
            $wallet->save();
            return Helpers::sendSuccess(translate('Withdraw request sent successfully!'), '');

        }

        return Helpers::sendError([['message' =>  translate('Invalid withdraw request')]], 400);
    }

    public function close_withdraw_request(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);
        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        $withdraw_request = WithdrawRequest::find($request['id']);
        $wallet = SellerWallet::where('seller_id', $seller['id'])->first();

        if (isset($withdraw_request) && $withdraw_request->approved == 0) {
            $wallet->total_earning += BackEndHelper::currency_to_aed($withdraw_request['amount']);
            $wallet->pending_withdraw -= BackEndHelper::currency_to_aed($request['amount']);
            $wallet->save();
            $withdraw_request->delete();
            return Helpers::sendSuccess(translate('Withdraw request has been closed successfully!'),'');
        }

        return Helpers::sendError([['message' => translate('Withdraw request is invalid')]], 401);
    }

    public function transaction(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);
        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        $transaction = WithdrawRequest::where('seller_id', $seller['id'])->latest()->get();

        return Helpers::sendSuccess("Data Got!", $transaction);
    }

    public function monthly_earning(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);
        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        $from = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');
        $seller_data = '';
        $seller_earnings = OrderTransaction::where([
            'seller_is' => 'seller',
            'seller_id' => $seller['id'],
            'status' => 'disburse'
        ])->select(
            DB::raw('IFNULL(sum(seller_amount),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $default = 0;
            foreach ($seller_earnings as $match) {
                if ($match['month'] == $inc) {
                    $default = $match['sums'];
                }
            }
            $seller_data .= $default . ',';
        }

        return Helpers::sendSuccess("Data Got!", $seller_data);
    }

    public function monthly_commission_given(Request $request)
    {
        $data = Helpers::get_seller_by_token($request);
        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        $from = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $commission_data = '';
        $commission_earnings = OrderTransaction::where([
            'seller_is' => 'seller',
            'seller_id' => $seller['id'],
            'status' => 'disburse'
        ])->select(
            DB::raw('IFNULL(sum(admin_commission),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $default = 0;
            foreach ($commission_earnings as $match) {
                if ($match['month'] == $inc) {
                    $default = $match['sums'];
                }
            }
            $commission_data .= $default . ',';
        }

        return Helpers::sendSuccess("Data Got!", $commission_data);
    }

    public function update_cm_firebase_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cm_firebase_token' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }
        $data = Helpers::get_seller_by_token($request);

        if ($data['success'] == 1) {
            $seller = $data['data'];
        } else {
            return Helpers::sendError([['message' => translate('Your existing session token does not authorize you any more')]], 401);
        }

        DB::table('sellers')->where('id', $seller->id)->update([
            'cm_firebase_token' => $request['cm_firebase_token'],
        ]);
        return Helpers::sendSuccess('successfully updated!', '');
    }
}
