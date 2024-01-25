<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CategoryManager;
use App\CPU\Helpers;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Resources\ProductBref;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function get_categories($id = null)
    {
        try {
            if (is_null($id)) {
                $categories = \App\Resources\Category::collection(Category::with(['childes.childes'])->where(['position' => 0])->get());
                return Helpers::sendSuccess(translate('Data Got!'), $categories);
            } else {
                $cat = Category::where('id', $id)->first();
                if (!$cat) {
                    $cat = Category::where('slug', $id)->first();
                }
                return Helpers::sendSuccess(translate('Data Got!'), \App\Resources\Category::make($cat));
            }
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 403);
        }
    }

    public function get_products($id, Request $request)
    {
        $cat = Category::where('id', $id)->first();
        $products = CategoryManager::products_paginate($id, $request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        if (!$cat) {
            $cat = Category::where('slug', $id)->first();
            if ($cat) {
                $products = CategoryManager::products_paginate($cat->id, $request['limit'], $request['offset']);
                $products['products'] = ProductBref::collection($products['products']);
            }
        }
        return Helpers::sendSuccess(translate('Data Got!'), $products);
    }
}
