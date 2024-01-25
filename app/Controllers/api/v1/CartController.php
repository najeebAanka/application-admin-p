<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CartManager;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class CartController extends Controller
{

    public function cart(Request $request)
    {
        $user = Helpers::get_customer($request);
        $cart = Cart::where(['customer_id' => $user->id])->get();
        $cart->map(function ($data) {
            $data['choices'] = json_decode($data['choices']);
            $data['variations'] = json_decode($data['variations']);
            return $data;
        });

        $shipping_cost = \App\CPU\CartManager::get_shipping_cost();
        $sub_total = 0;
        $total_tax = 0;
        $total_discount_on_product = 0;
        $total_shipping_cost = 0;
        if ($cart->count() > 0) {
            foreach ($cart as $key => $cartItem) {
                $sub_total += $cartItem['price'] * $cartItem['quantity'];
                $total_tax += $cartItem['tax'] * $cartItem['quantity'];
                $total_discount_on_product += $cartItem['discount'] * $cartItem['quantity'];
            }
            $total_shipping_cost = $shipping_cost;
        }
        if (session()->has('coupon_discount')) {
            $coupon_dis = session('coupon_discount');
        } else {
            $coupon_dis = 0;
        }
        return Helpers::sendSuccess('Data Got!', [
            'sub_total' => (double)$sub_total,
            'sub_total_formated' => \App\CPU\Helpers::currency_converter($sub_total),
            'total_tax' => (double)$total_tax,
            'total_tax_formated' => \App\CPU\Helpers::currency_converter($total_tax),
            'total_discount_on_product' => (double)$total_discount_on_product,
            'total_discount_on_product_formated' => \App\CPU\Helpers::currency_converter($total_discount_on_product),
            'total_shipping_cost' => (double)$total_shipping_cost,
            'total_shipping_cost_formated' => \App\CPU\Helpers::currency_converter($total_shipping_cost),
            'total' => (double)($sub_total + $total_tax + $total_shipping_cost - $coupon_dis - $total_discount_on_product),
            'total_formated' =>
                \App\CPU\Helpers::currency_converter(
                    $sub_total + $total_tax + $total_shipping_cost - $coupon_dis - $total_discount_on_product),
            'cart' => $cart
        ]);
    }

    public function add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'quantity' => 'required',
        ], [
            'id.required' => translate('Product ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $cart = CartManager::add_to_cart($request);
        return Helpers::sendSuccess($cart['message'], '');

    }

    public function update_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required',
            'quantity' => 'required',
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }
        $response = CartManager::update_cart_qty($request);

        return Helpers::sendSuccess($response['message'], [
            'qty' => $response['qty']
        ]);
    }

    public function remove_from_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required'
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);


        if ($validator->errors()->count() > 0) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }
        
        $user = Helpers::get_customer($request);
        Cart::where(['id' => $request->key, 'customer_id' => $user->id])->delete();
        return Helpers::sendSuccess(translate('successfully_removed'), '');
    }
}
