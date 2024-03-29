<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\CPU\ProductManager;
use App\Http\Controllers\Controller;
use App\Model\Seller;
use App\Model\Shop;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function get_seller_info(Request $request)
    {
        $seller = Seller::with(['shop'])->where(['id' => $request['seller_id']])->first(['id', 'f_name', 'l_name', 'phone', 'image']);
        return Helpers::sendSuccess("Data Got!", $seller);
    }

    public function get_seller_products($seller_id, Request $request)
    {
        $data = ProductManager::get_seller_products($seller_id, $request['limit'], $request['offset']);
        $data['products'] = Helpers::product_data_formatting($data['products'], true);

        return Helpers::sendSuccess("Data Got!", $data);
    }

    public function get_top_sellers()
    {
        $top_sellers = Shop::whereHas('seller', function ($query) {
            return $query->approved();
        })->take(15)->get();
        return Helpers::sendSuccess("Data Got!",$top_sellers);
    }

    public function get_all_sellers()
    {
        $top_sellers = Shop::whereHas('seller', function ($query) {
            return $query->approved();
        })->get();
        return Helpers::sendSuccess("Data Got!",$top_sellers);
    }
}
