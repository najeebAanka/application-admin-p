<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use App\Model\Category;
use App\Model\Product;

class POSController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category_id', 0);
        $keyword = $request->query('search', false);
        $categories = Category::get();

        $key = explode(' ', $keyword);
        $products = Product::where('added_by','admin')
            ->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
                $query->whereJsonContains('category_ids', [[['id' => (string)$request['category_id']]]]);
            })
            ->when($keyword, function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->paginate(Helpers::pagination_limit());
            
        return view('admin-views.pos.index',compact('categories','category','keyword','products'));
    }
    public function quick_view(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos._quick-view-data', compact('product'))->render(),
        ]);
    }
}
