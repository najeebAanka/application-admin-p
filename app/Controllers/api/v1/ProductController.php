<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CategoryManager;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\CPU\ProductManager;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\HomeSection;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Review;
use App\Model\ShippingMethod;
use App\Model\Wishlist;
use App\Resources\Banner;
use App\Resources\ProductBref;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class ProductController extends Controller
{
    public function products(Request $request)
    {
        $products = ProductManager::get_latest_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }

    public function home_sections(Request $request)
    {

        $products = ProductManager::get_best_selling_products($request['limit'], $request['offset']);
        $best_sell = ProductBref::collection($products['products']);

        $data = [
            [
                'id' => -1,
                'title' => translate('best_sell'),
                'slug' => '',
                'icon' => asset('assets/images/best_sell.png'),
                'childes' => [],
                'products_style' => (int)3,
                'products' => $best_sell
            ]
        ];

        $home_section_categories = Category::where('home_section', 1)->get();
        foreach ($home_section_categories as $c) {
            $products = ProductBref::collection(CategoryManager::products_limited($c->id));
            $childes = \App\Resources\Category::collection($c->childes);
            $cat_childes = [];
            for ($i = 0; $i < sizeof($childes); $i++) {
                $cat_products = ProductBref::collection(CategoryManager::products_limited($childes[$i]->id));
                array_push($cat_childes, [
                    'id' => $childes[$i]->id,
                    'title' => $childes[$i]->name,
                    'slug' => $childes[$i]->slug,
                    'icon' => asset('storage/category') . '/' . $childes[$i]->icon,
                    'childes' => [],
                    'products_style' => (int)$childes[$i]->products_style,
                    'products' => $cat_products
                ]);
            }
            array_push($data, [
                'id' => $c->id,
                'title' => $c->name,
                'slug' => $c->slug,
                'icon' => asset('storage/category') . '/' . $c->icon,
                'childes' => $cat_childes,
                'products_style' => (int)$c->products_style,
                'products' => $products
            ]);
        }

        if (sizeof($data[0]['products']) == 0) {
            $data[0]['products'] = $data[1]['products'];
        }

        return Helpers::sendSuccess("Data Got!", $data);
    }


    public function home_sections_mobile(Request $request)
    {


        $hs = HomeSection::where('published', 1)->orderBy('position', 'asc')->get();

        $data = [];

        foreach ($hs as $h) {

            $hs_banner = null;
            $hs_products = [];

            if ($h->resource_type == 'Best Sell') {
                $hs_products = ProductManager::get_best_selling_products($request['limit'], $request['offset']);
                $hs_products = ProductBref::collection($hs_products['products']);
                if (sizeof($hs_products) == 0) {
                    $cat = Category::first();
                    if ($cat) {
                        $hs_products = ProductBref::collection(CategoryManager::products_limited($cat->id));
                    }
                }
            } elseif ($h->resource_type == 'category') {
                $hs_products = ProductBref::collection(CategoryManager::products_limited($h->resource_id));
            } elseif ($h->resource_type == 'banner') {
                $banner = \App\Model\Banner::where('id', $h->resource_id)->first();
                if ($banner) {
                    $hs_banner = Banner::make($banner);
                }
            }

            array_push($data, [
                'id' => $h->id,
                'home_section_type' => $h->home_section_type,
                'resource_type' => $h->resource_type,
                'title' => \App\CPU\translate($h->title),
                'photo' => asset('storage/home-section') . '/' . $h->photo,
                'products_style' => $h->home_section_type,
                'hs_banner' => $hs_banner,
                'hs_products' => $hs_products
            ]);
        }


        return Helpers::sendSuccess("Data Got!", $data);
    }


    public function get_latest_products(Request $request)
    {
        $products = ProductManager::get_latest_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }

    public function get_featured_products(Request $request)
    {
        $products = ProductManager::get_featured_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }

    public function get_top_rated_products(Request $request)
    {
        $products = ProductManager::get_top_rated_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }

    public function get_searched_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $products = ProductManager::search_products($request['name'], $request['limit'], $request['offset']);
        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search($request['name'], $request['limit'], $request['offset']);
        }
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }

    public function get_product($id)
    {
        $product = Product::where('id', $id)->orWhere('slug', $id)->first();
        if (isset($product)) {
            $product = \App\Resources\Product::make($product);
        }
        return Helpers::sendSuccess('Data Got!', $product);
    }

    public function get_best_sellings(Request $request)
    {
        $products = ProductManager::get_best_selling_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }

    public function get_home_categories()
    {
        $categories = Category::where('home_status', true)->get();
        $categories->map(function ($data) {
            $data['products'] = Helpers::product_data_formatting(CategoryManager::products($data['id']), true);
            return $data;
        });
        return Helpers::sendSuccess("Data Got!", $categories);
    }

    public function get_related_products($id)
    {
        if (Product::find($id)) {
            $products = ProductManager::get_related_products($id);
            $products = Helpers::product_data_formatting($products, true);
            return Helpers::sendSuccess("Data Got!", $products);
        }

        return Helpers::sendError([['code' => 'product-001', 'message' => translate('Product not found!')]], 404);
    }

    public function get_product_reviews($id)
    {
        $reviews = Review::with(['customer'])->where(['product_id' => $id])->get();

        return Helpers::sendSuccess("Data Got!", \App\Resources\Review::collection($reviews));
    }

    public function get_product_rating($id)
    {
        try {
            $product = Product::find($id);
            $overallRating = \App\CPU\ProductManager::get_overall_rating($product->reviews);

            return Helpers::sendSuccess('Data Got!', floatval($overallRating[0]));
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function counter($product_id)
    {
        try {
            $countOrder = OrderDetail::where('product_id', $product_id)->count();
            $countWishlist = Wishlist::where('product_id', $product_id)->count();

            return Helpers::sendSuccess('Data Got!', ['order_count' => $countOrder, 'wishlist_count' => $countWishlist]);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function social_share_link($product_id)
    {
        $product = Product::find($product_id);
        $link = route('product', $product->slug);
        try {

            return Helpers::sendSuccess("Data Got!", $link);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function submit_product_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return Helpers::sendError(Helpers::error_processor($validator), 403);
        }

        $image_array = [];
        if (!empty($request->file('fileUpload'))) {
            foreach ($request->file('fileUpload') as $image) {
                if ($image != null) {
                    array_push($image_array, ImageManager::upload('review/', 'png', $image));
                }
            }
        }

        $review = new Review;
        $review->customer_id = $request->user()->id;
        $review->product_id = $request->product_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($image_array);
        $review->save();
        return Helpers::sendSuccess(translate('successfully review submitted!'), '');
    }

    public function get_shipping_methods(Request $request)
    {
        $methods = ShippingMethod::where(['status' => 1])->get();
        return Helpers::sendSuccess("Data Got!", $methods);
    }

    public function get_discounted_product(Request $request)
    {
        $products = ProductManager::get_discounted_product($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess("Data Got!", $products);
    }
}
