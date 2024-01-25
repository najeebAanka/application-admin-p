<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\Model\FlashDeal;
use App\Model\FlashDealProduct;
use App\Model\Product;

class FlashDealController extends Controller
{
    public function get_flash_deal()
    {
        try {
            $flash_deals = FlashDeal::where(['status' => 1])
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))->first();

            return Helpers::sendSuccess(translate("Data Got!"), $flash_deals);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }

    }

    public function get_products($deal_id)
    {
        $p_ids = FlashDealProduct::with(['product'])
            ->where(['flash_deal_id' => $deal_id])
            ->pluck('product_id')->toArray();
        if (count($p_ids) > 0) {
            return Helpers::sendSuccess(translate('Data Got!'), Helpers::product_data_formatting(Product::with(['rating'])->whereIn('id', $p_ids)->get(), true), '');
        }
        return Helpers::sendSuccess(translate('Dta not found!'), '');
    }
}
