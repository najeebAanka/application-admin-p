<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\CPU\ProductManager;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\Model\FavSeller;
use App\Model\Seller;
use App\Model\Shop;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function get_seller_info(Request $request)
    {
        $seller = Seller::with(['shop'])->where(['id' => $request['seller_id']])->first(['id', 'f_name', 'l_name', 'phone', 'image']);
        return Helpers::sendSuccess(translate("Data Got!"), $seller);
    }

    public function get_seller_products($seller_id, Request $request)
    {
        $data = ProductManager::get_seller_products($seller_id, $request['limit'], $request['offset']);
        $data['products'] = Helpers::product_data_formatting($data['products'], true);

        return Helpers::sendSuccess(translate("Data Got!"), $data);
    }

    public function get_top_sellers()
    {
        $top_sellers = Shop::whereHas('seller', function ($query) {
            return $query->approved();
        })->take(15)->get();
        return Helpers::sendSuccess(translate("Data Got!"), $top_sellers);
    }

    public function get_all_sellers()
    {
        $top_sellers = Shop::whereHas('seller', function ($query) {
            return $query->approved();
        })->get();
        return Helpers::sendSuccess(translate("Data Got!"), $top_sellers);
    }

    public function fav()
    {
        if (auth('api')->check()) {
            $data = FavSeller::where('user_id', auth('api')->user()->id)->get();
            return Helpers::sendSuccess(translate("Data Got!"), \App\Resources\FavSeller::collection($data));
        } else {
            return Helpers::sendError([['message' => translate("Login First!")]], 403);

        }
    }


    public function get_all_fav_sellers(Request $request)
    {
        if (auth('api')->check()) {
            $data = FavSeller::where('seller_id', $request->seller_id)->first();
            if ($data) {
                $data->delete();
                return Helpers::sendSuccess("Removed From Fav!", null);
            } else {
                if (Seller::where('id', $request->seller_id)->first()) {
                    $data = new FavSeller();
                    $data->seller_id = $request->seller_id;
                    $data->user_id = auth('api')->user()->id;
                    $data->save();
                    return Helpers::sendSuccess("Added to Fav!", null);
                } else {
                    return Helpers::sendSuccess("Seller Not Found!", null);
                }

            }
        } else {
            return Helpers::sendError([['message' => translate("Login First!")]], 403);

        }
    }
}
