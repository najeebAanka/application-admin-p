<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CategoryManager;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\CPU\ProductManager;
use App\Http\Controllers\Controller;
use App\Model\Attribute;
use App\Model\Brand;
use App\Model\Category;
use App\Model\HomeSection;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\ProductView;
use App\Model\Review;
use App\Model\ShippingMethod;
use App\Model\Transaction;
use App\Model\Wishlist;
use App\Resources\Banner;
use App\Resources\ProductBref;
use App\Resources\ProductSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class ProductController extends Controller
{
    public function filters(Request $request)
    {


        $products = Product::select(['products.*', 'translations.value as t_v'])->
        leftjoin('translations', 'translations.translationable_id', '=', 'products.id')->
        where('translations.key', 'name')->
        where('translations.translationable_type', 'App\Model\Product')->active();

        $products = ProductManager::doProductFilters($request, $products);


        $products = $products->get();

        $brands = [];
        $attributes = [];
        $colors = [];
        $min_price = 500000;
        $max_price = 0;
        foreach ($products as $pd) {
            if (!in_array($pd->brand_id, $brands)) {
                array_push($brands, $pd->brand_id);
            }
            $atr = json_decode($pd->attributes);
            if (is_array($atr)) {
                foreach ($atr as $a) {
                    if (!in_array($a, $attributes)) {
                        array_push($attributes, $a);
                    }
                }
            }
            $cols = json_decode($pd->colors);
            if (is_array($cols)) {
                foreach ($cols as $col) {
                    if (!in_array($col, $colors)) {
                        array_push($colors, $col);
                    }
                }
            }
            if ($pd->unit_price > $max_price) {
                $max_price = $pd->unit_price;
            }
            if ($pd->unit_price < $min_price) {
                $min_price = $pd->unit_price;
            }
        }
        $brands = \App\Resources\Brand::collection(Brand::whereIn('id', $brands)->get());
        $attributes = Attribute::whereIn('id', $attributes)->get();
        foreach ($attributes as $atr) {
            $options = [];
            foreach ($products as $pa) {
                $choice_options = json_decode($pa['choice_options'], true);
                foreach ($choice_options as $key => $choice) {
                    $trans = Attribute::
                    join('translations', 'translations.translationable_id', '=', 'attributes.id')->
                    where('translations.translationable_type', 'App\Model\Attribute')->
                    where('name', 'like', "{$atr->name}")->orWhere('translations.value', 'like', "{$atr->name}")->first();
                    if ($trans) {
                        if ($choice['title'] == $trans->name) {
                            foreach ($choice['options'] as $key => $option) {
                                if (!in_array(strtolower(trim($option)), $options)) {
                                    array_push($options, strtolower(trim($option)));
                                }
                            }
                        }
                    }

                }
            }
            $atr->options = $options;
        }
        $attributes = \App\Resources\Attribute::collection($attributes);

        //prices
        if ($max_price > $min_price) {
            $diff = (Helpers::currency_converter($max_price, false) - Helpers::currency_converter($min_price, false)) / 4;
            $prices = [
                [
                    'min_price' => 'end',
                    'max_price' => round($diff * 2, 0),
                    'text' => trans('messages.less_price', ['value' => round($diff * 2, 0)])
                ],
                [
                    'min_price' => round($diff * 2, 0),
                    'max_price' => round($diff * 3, 0),
                    'text' => trans('messages.between_price', ['value1' => round($diff * 2, 0), 'value2' => round($diff * 3, 0)])
                ],
                [
                    'min_price' => round($diff * 3, 0),
                    'max_price' => round($diff * 4, 0),
                    'text' => trans('messages.between_price', ['value1' => round($diff * 3, 0), 'value2' => round($diff * 4, 0)])
                ],
                [
                    'min_price' => round($diff * 4, 0),
                    'max_price' => 'end',
                    'text' => trans('messages.more_price', ['value' => round($diff * 4, 0)])
                ]
            ];
        } else {
            $diff = 0;
            $prices = [];
        }

        $data = [
            'brands' => $brands,
            'attributes' => $attributes,
            'colors' => $colors,
            'prices' => $prices
        ];

        return Helpers::sendSuccess(translate("Data Got!"), $data);
    }


    public function autoComplete(Request $request)
    {
        $products = Product::select(['products.*', 'translations.value as t_v'])->
        leftjoin('translations', 'translations.translationable_id', '=', 'products.id')->
        where('translations.translationable_type', 'App\Model\Product')->
        where('translations.key', 'name')->
        active();

        if ($request['search_text'] && strlen($request['search_text']) > 3) {
            $key = explode(' ', $request['search_text']);
            $products = $products->
            where(function ($q) use ($key) {
                foreach ($key as $l => $value) {
                    if ($l == 0) {
                        $q->where(function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%")->orWhere('translations.value', 'like', "%{$value}%");
                        });
                    } else {
                        $q->orWhere(function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%")->orWhere('translations.value', 'like', "%{$value}%");
                        });
                    }
                }
            });

            $products = $products->skip(0)->take(10)->get();
        } else {
            $products = [];
        }

        return Helpers::sendSuccess(translate("Data Got!"), ProductSearch::collection($products));

    }


    public function products(Request $request)
    {

        $request['sort_by'] == null ? $request['sort_by'] == 'latest' : $request['sort_by'];

        $limit = 20;
        $offset = 1;

        if (isset($request['limit'])) {
            $limit = $request['limit'];
        }
        if (isset($request['offset'])) {
            $offset = $request['offset'];
        }

        $products = Product::select(['products.*', 'translations.value as t_v'])->
        leftjoin('translations', 'translations.translationable_id', '=', 'products.id')->
        where('translations.translationable_type', 'App\Model\Product')->
        where('translations.key', 'name')->
        active()->with(['reviews']);

        $products = ProductManager::doProductFilters($request, $products);

        if ($request['sort_by'] == 'latest') {
            $products = $products->latest();
        } elseif ($request['sort_by'] == 'low-high') {
            $products = $products->orderBy('unit_price', 'ASC');
        } elseif ($request['sort_by'] == 'high-low') {
            $products = $products->orderBy('unit_price', 'DESC');
        } elseif ($request['sort_by'] == 'a-z') {
            $products = $products->orderBy('name', 'ASC');
        } elseif ($request['sort_by'] == 'z-a') {
            $products = $products->orderBy('name', 'DESC');
        } else {
            $products = $products;
        }

        $products = $products->paginate($limit, ['*'], 'page', $offset);

        $products = [
            'total_size' => $products->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => \App\Resources\Product::collection($products->items())
        ];

        return Helpers::sendSuccess(translate("Data Got!"), $products);
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
                    'title' => translate($childes[$i]->name),
                    'slug' => $childes[$i]->slug,
                    'icon' => asset('storage/category') . '/' . $childes[$i]->icon,
                    'childes' => [],
                    'products_style' => (int)$childes[$i]->products_style,
                    'products' => $cat_products
                ]);
            }
            array_push($data, [
                'id' => $c->id,
                'title' => translate($c->name),
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

        return Helpers::sendSuccess(translate("Data Got!"), $data);
    }


    public function home_sections_mobile(Request $request)
    {


        $hs = HomeSection::where('published', 1);

        if ($request->has('is_mad_test')) {
            $hs = $hs->where('is_mad_test', 1);
        }

        $hs = $hs->orderBy('position', 'asc')->get();

        $data = [];

        foreach ($hs as $h) {

            $hs_banner = null;
            $hs_products = [];
            $hs_offers = [];

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
            } elseif ($h->resource_type == 'offers') {
                $banner = \App\Model\Banner::where('banner_type', 'Offer Banner')->get();
                if ($banner) {
                    $hs_offers = Banner::collection($banner);
                }
            }

            array_push($data, [
                'id' => $h->id,
                'home_section_type' => $h->home_section_type,
                'color' => $h->color,
                'resource_type' => $h->resource_type,
                'title' => \App\CPU\translate($h->title),
                'photo' => asset('storage/home-section') . '/' . $h->photo,
                'products_style' => $h->home_section_type,
                'hs_banner' => $hs_banner,
                'hs_offers' => $hs_offers,
                'hs_products' => $hs_products
            ]);
        }


        return Helpers::sendSuccess(translate("Data Got!"), $data);
    }


    public function get_latest_products(Request $request)
    {
        $products = ProductManager::get_latest_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess(translate("Data Got!"), $products);
    }

    public function recentlyViewed(Request $request)
    {
        $products = ProductManager::get_recentyl_viewd($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess(translate("Data Got!"), $products);
    }

    public function get_featured_products(Request $request)
    {
        $products = ProductManager::get_featured_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess(translate("Data Got!"), $products);
    }

    public function get_top_rated_products(Request $request)
    {
        $products = ProductManager::get_top_rated_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess(translate("Data Got!"), $products);
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
        return Helpers::sendSuccess(translate("Data Got!"), $products);
    }

    public function get_product($id)
    {
        $product = Product::where('id', $id)->orWhere('slug', $id)->first();
        if (isset($product)) {
            $product = \App\Resources\Product::make($product);
            if (auth('api')->check()) {
                $pv = new ProductView();
                $pv->user_id = auth('api')->user()->id;
                $pv->product_id = $product->id;
                $pv->save();
            }
        }
        return Helpers::sendSuccess(translate('Data Got!'), $product);
    }

    public function get_best_sellings(Request $request)
    {
        $products = ProductManager::get_best_selling_products($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess(translate("Data Got!"), $products);
    }

    public function get_home_categories()
    {
        $categories = Category::where('home_status', true)->get();
        $categories->map(function ($data) {
            $data['products'] = Helpers::product_data_formatting(CategoryManager::products($data['id']), true);
            return $data;
        });
        return Helpers::sendSuccess(translate("Data Got!"), $categories);
    }

    public function get_related_products($id)
    {
        if (Product::find($id)) {
            $products = ProductManager::get_related_products($id);
            $products = Helpers::product_data_formatting($products, true);
            return Helpers::sendSuccess(translate("Data Got!"), $products);
        }

        return Helpers::sendError([['code' => 'product-001', 'message' => translate('Product not found!')]], 404);
    }

    public function get_product_reviews($id)
    {
        $reviews = Review::with(['customer'])->where(['product_id' => $id])->get();

        return Helpers::sendSuccess(translate("Data Got!"), \App\Resources\Review::collection($reviews));
    }

    public function get_product_rating($id)
    {
        try {
            $product = Product::find($id);
            $overallRating = \App\CPU\ProductManager::get_overall_rating($product->reviews);

            return Helpers::sendSuccess(translate('Data Got!'), floatval($overallRating[0]));
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function counter($product_id)
    {
        try {
            $countOrder = OrderDetail::where('product_id', $product_id)->count();
            $countWishlist = Wishlist::where('product_id', $product_id)->count();

            return Helpers::sendSuccess(translate('Data Got!'), ['order_count' => $countOrder, 'wishlist_count' => $countWishlist]);
        } catch (\Exception $e) {
            return Helpers::sendError([['message' => $e->getMessage()]], 401);
        }
    }

    public function social_share_link($product_id)
    {
        $product = Product::find($product_id);
        $link = route('product', $product->slug);
        try {

            return Helpers::sendSuccess(translate("Data Got!"), $link);
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
        return Helpers::sendSuccess(translate("Data Got!"), $methods);
    }

    public function get_discounted_product(Request $request)
    {
        $products = ProductManager::get_discounted_product($request['limit'], $request['offset']);
        $products['products'] = ProductBref::collection($products['products']);
        return Helpers::sendSuccess(translate("Data Got!"), $products);
    }
}
