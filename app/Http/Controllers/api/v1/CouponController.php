<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\Model\Coupon;
use App\Model\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {

        try {
            $couponLimit = Order::where('customer_id', $request->user()->id)
                ->where('coupon_code', $request['code'])->count();

            $coupon = Coupon::where(['code' => $request['code']])
                ->where('limit', '>', $couponLimit)
                ->where('status', '=', 1)
                ->whereDate('start_date', '<=', Carbon::parse()->toDateString())
                ->whereDate('expire_date', '>=', Carbon::parse()->toDateString())->first();
            //$coupon = Coupon::where(['code' => $request['code']])->first();


        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 403);

        }

        return Helpers::sendSuccess(translate('Data Got!'), $coupon);

    }

    public function getUsed(Request $request)
    {

        try {

            $orders = Order::select('coupon_code')->where('customer_id', auth('api')->user()->id)->
            whereNotNull('coupon_code')->pluck('coupon_code');
            $coupons = \App\Resources\Coupon::collection(Coupon::where('code', $orders)->get());

        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 403);

        }

        return Helpers::sendSuccess(translate('Data Got!'), $coupons);

    }
}
