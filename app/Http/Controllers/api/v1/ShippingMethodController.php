<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CartManager;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\CartShipping;
use App\Model\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class ShippingMethodController extends Controller
{
    public function get_shipping_method_info($id)
    {
        try {
            $shipping = ShippingMethod::find($id);
            return Helpers::sendSuccess(translate("Data Got!"),$shipping);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function shipping_methods_by_seller($id, $seller_is)
    {
        $seller_is = $seller_is == 'admin' ? 'admin' : 'seller';
        return Helpers::sendSuccess(translate('Data got!'),Helpers::get_shipping_methods($id, $seller_is));
    }

    public function choose_for_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_group_id' => 'required',
            'id' => 'required'
        ], [
            'id.required' => translate('shipping_id_is_required')
        ]);

        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        if ($request['cart_group_id'] == 'all_cart_group') {
            foreach (CartManager::get_cart_group_ids($request) as $group_id) {
                $request['cart_group_id'] = $group_id;
                self::insert_into_cart_shipping($request);
            }
        } else {
            self::insert_into_cart_shipping($request);
        }
        return Helpers::sendSuccess(translate('successfully_added'),'');
    }

    public static function insert_into_cart_shipping($request)
    {
        $shipping = CartShipping::where(['cart_group_id' => $request['cart_group_id']])->first();
        if (isset($shipping) == false) {
            $shipping = new CartShipping();
        }
        $shipping['cart_group_id'] = $request['cart_group_id'];
        $shipping['shipping_method_id'] = $request['id'];
        $shipping['shipping_cost'] = ShippingMethod::find($request['id'])->cost;
        $shipping->save();
    }

    public function chosen_shipping_methods(Request $request)
    {
        $group_ids = CartManager::get_cart_group_ids($request);
        return Helpers::sendSuccess(translate('Data Got!'),CartShipping::whereIn('cart_group_id', $group_ids)->get());
    }
}
