<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\BrandManager;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Brand;
use App\Resources\ProductBref;
use function App\CPU\translate;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function get_brands()
    {
        try {
            $brands = BrandManager::get_brands();
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 403);
        }

        return Helpers::sendSuccess(translate('Data Got!'), Brand::collection($brands));
    }

    public function get_products($brand_id)
    {
        try {
            $products = BrandManager::get_products($brand_id);
            $products['products'] = Productbref::collection($products['products']);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 403);
        }

        return Helpers::sendSuccess(translate('Data Got!'), $products);

    }
}
