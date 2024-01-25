<?php

namespace App\CPU;

use App\Model\Category;
use App\Model\Product;
use Illuminate\Support\Facades\Log;

class CategoryManager
{
    public static function parents()
    {
        $x = Category::with(['childes.childes'])->where('position', 0)->get();
        return $x;
    }

    public static function child($parent_id)
    {
        $x = Category::where(['parent_id' => $parent_id])->get();
        return $x;
    }

    public static function products($category_id)
    {
        return Product::active()
            /*->where('category_ids', 'like', "%{$data['id']}%")*/
            ->whereJsonContains('category_ids', ["id" => (string)$category_id])->get();
    }

    public static function products_paginate($category_id, $limit = 10, $offset = 1)
    {
        $paginator = Product::active()
            /*->where('category_ids', 'like', "%{$data['id']}%")*/
            ->whereJsonContains('category_ids', ["id" => (string)$category_id])->latest()->paginate($limit, ['*'], 'page', $offset);


        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }


    public static function products_limited($category_id, $limit = 12)
    {
        return Product::active()
            /*->where('category_ids', 'like', "%{$data['id']}%")*/
            ->whereJsonContains('category_ids', ["id" => (string)$category_id])->orderBy('id', 'desc')->skip(0)->take($limit)->get();
    }
}
