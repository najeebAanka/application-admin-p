<?php

namespace App\CPU;

use App\Model\Attribute;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\ProductView;
use App\Model\Review;
use App\Model\ShippingMethod;
use App\Model\Translation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductManager
{
    public static function get_product($id)
    {
        return Product::active()->with(['rating'])->where('id', $id)->first();
    }

    public static function get_latest_products($limit = 10, $offset = 1)
    {
        $paginator = Product::active()->with(['rating'])->latest()->paginate($limit, ['*'], 'page', $offset);
        /*$paginator->count();*/
        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function get_recentyl_viewd($limit = 10, $offset = 1)
    {
        $paginator = Product::active()->whereIn('id',
            ProductView::distinct()->where('user_id', auth('api')->user()->id)->orderBy('id', 'desc')->skip(0)->take(20)->pluck('product_id')
        )->with(['rating'])->latest()->paginate($limit, ['*'], 'page', $offset);
        /*$paginator->count();*/
        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }


    public static function get_featured_products($limit = 10, $offset = 1)
    {
        $paginator = Product::with(['reviews'])->active()
            ->where('featured', 1)
            ->withCount(['order_details'])->orderBy('order_details_count', 'DESC')
            ->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function get_top_rated_products($limit = 10, $offset = 1)
    {
        $reviews = Review::with('product')
            ->whereHas('product', function ($query) {
                $query->active();
            })
            ->select('product_id', DB::raw('AVG(rating) as count'))
            ->groupBy('product_id')
            ->orderBy("count", 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [];
        foreach ($reviews as $review) {
            array_push($data, $review->product);
        }

        return [
            'total_size' => $reviews->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $data
        ];
    }

    public static function get_best_selling_products($limit = 10, $offset = 1)
    {
        $paginator = OrderDetail::with('product.reviews')
            ->whereHas('product', function ($query) {
                $query->active();
            })
            ->select('product_id', DB::raw('COUNT(product_id) as count'))
            ->groupBy('product_id')
            ->orderBy("count", 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [];
        foreach ($paginator as $order) {
            array_push($data, $order->product);
        }

        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $data
        ];
    }

    public static function get_related_products($product_id)
    {
        $product = Product::find($product_id);
        return Product::active()->where('brand_id', $product->brand_id)
            ->where('id', '!=', $product->id)
            ->limit(12)
            ->get();
    }

    public static function search_products($name, $limit = 10, $offset = 1)
    {
        /*$key = explode(' ', $name);*/
        $key = [$name];

        $paginator = Product::active()->with(['rating'])->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function translated_product_search($name, $limit = 10, $offset = 1)
    {
        $product_ids = Translation::where('translationable_type', 'App\Model\Product')
            ->where('key', 'name')
            ->where('value', 'like', "%{$name}%")
            ->pluck('translationable_id');

        $paginator = Product::WhereIn('id', $product_ids)->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function product_image_path($image_type)
    {
        $path = '';
        if ($image_type == 'thumbnail') {
            $path = asset('storage/product/thumbnail');
        } elseif ($image_type == 'product') {
            $path = asset('storage/product');
        }
        return $path;
    }

    public static function get_product_review($id)
    {
        $reviews = Review::where('product_id', $id)
            ->where('status', 1)->get();
        return $reviews;
    }

    public static function get_rating($reviews)
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;
        foreach ($reviews as $key => $review) {
            if ($review->rating == 5) {
                $rating5 += 1;
            }
            if ($review->rating == 4) {
                $rating4 += 1;
            }
            if ($review->rating == 3) {
                $rating3 += 1;
            }
            if ($review->rating == 2) {
                $rating2 += 1;
            }
            if ($review->rating == 1) {
                $rating1 += 1;
            }
        }
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public static function get_overall_rating($reviews)
    {
        $totalRating = count($reviews);
        $rating = 0;
        foreach ($reviews as $key => $review) {
            $rating += $review->rating;
        }
        if ($totalRating == 0) {
            $overallRating = 0;
        } else {
            $overallRating = number_format($rating / $totalRating, 2);
        }

        return [$overallRating, $totalRating];
    }

    public static function get_shipping_methods($product)
    {
        if ($product['added_by'] == 'seller') {
            $methods = ShippingMethod::where(['creator_id' => $product['user_id']])->where(['status' => 1])->get();
            if ($methods->count() == 0) {
                $methods = ShippingMethod::where(['creator_type' => 'admin'])->where(['status' => 1])->get();
            }
        } else {
            $methods = ShippingMethod::where(['creator_type' => 'admin'])->where(['status' => 1])->get();
        }

        return $methods;
    }

    public static function get_seller_products($seller_id, $limit = 10, $offset = 1)
    {
        $paginator = Product::active()->with(['rating'])
            ->where(['user_id' => $seller_id, 'added_by' => 'seller'])
            ->paginate($limit, ['*'], 'page', $offset);
        /*$paginator->count();*/
        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function get_discounted_product($limit = 10, $offset = 1)
    {
        $paginator = Product::with(['reviews'])->active()->where('discount', '!=', 0)->latest()->paginate($limit, ['*'], 'page', $offset);
        return [
            'total_size' => $paginator->total(),
            'limit' => (integer)$limit,
            'offset' => (integer)$offset,
            'products' => $paginator->items()
        ];
    }


    public static function doProductFilters($request, $products)
    {
        if ($request['search_text'] && strlen($request['search_text']) > 0) {
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
        }

        if ($request->has('categories') && strlen($request->get('categories')) > 0) {
            $categories = json_decode($request->get('categories'));
            $products = $products->where(function ($query) use ($categories) {
                if (is_array($categories) && sizeof($categories) > 0) {
                    $query = $query->whereJsonContains('category_ids', [['id' => (string)$categories[0]]]);
                    for ($i = 1; $i < sizeof($categories); $i++) {
                        $query = $query->orWhereJsonContains('category_ids', [['id' => (string)$categories[$i]]]);
                    }
                }
                return $query;
            });
        }

        if ($request->has('brands') && strlen($request->get('brands')) > 0) {
            $brands = json_decode($request->get('brands'));
            Log::info($brands);
            $products = $products->where(function ($query) use ($brands) {
                if (is_array($brands) && sizeof($brands) > 0) {
                    $query = $query->where('brand_id', $brands[0]);
                    for ($i = 1; $i < sizeof($brands); $i++) {
                        $query = $query->orWhere('brand_id', $brands[$i]);
                    }
                }
                return $query;
            });
        }

        if ($request->has('colors') && strlen($request->get('colors')) > 0) {
            $colors = json_decode($request->get('colors'));
            Log::info($colors);
            $products = $products->where(function ($query) use ($colors) {
                try {
                    if (is_array($colors) && sizeof($colors) > 0) {
                        $query = $query->whereJsonContains('colors', [(string)$colors[0]]);
                        for ($i = 1; $i < sizeof($colors); $i++) {
                            $query = $query->orWhereJsonContains('colors', [(string)$colors[$i]]);
                        }
                    }
                } catch (\Exception $ex) {

                }
                return $query;
            });
        }
        if ($request->has('attributes') && strlen($request->get('attributes')) > 0) {
            $attributes_filters = json_decode($request->get('attributes'));
            Log::info($attributes_filters);
            if (is_array($attributes_filters) && sizeof($attributes_filters) > 0) {
                foreach ($attributes_filters as $attributes_filter) {
                    foreach ($attributes_filter as $atrr_name => $attr) {
                        $trans = Attribute::
                        join('translations', 'translations.translationable_id', '=', 'attributes.id')->
                        where('translations.translationable_type', 'App\Model\Attribute')->
                        where('name', 'like', "{$atrr_name}")->orWhere('translations.value', 'like', "{$atrr_name}")->first();
                        if ($trans) {
                            $atrr_name = $trans->name;
                            $products = $products->where(function ($q) use ($atrr_name, $attr) {
                                try {
                                    $q = $q->where('choice_options', 'like', '%' . $atrr_name . '%');
                                    if (is_array($attr)) {
                                        foreach ($attr as $atr) {
                                            $q = $q->where('choice_options', 'like', '%' . $atr . '%');
                                        }
                                    }
                                } catch (\Exception $ex) {

                                }
                                return $q;
                            });
                        }
                    }
                }
            }
        }


        if ($request->has('prices') && strlen($request->get('prices')) > 0) {
            $prices = json_decode($request->get('prices'));
            Log::info($prices);
            if (sizeof($prices)) {
                $products = $products->where(function ($query) use ($prices) {
                    if (is_array($prices) && sizeof($prices) > 0) {
                        $price = explode('-', $prices[0]);
                        $min_price = $price[0];
                        $max_price = $price[1];
                        if ($min_price == 'end') {
                            $query = $query->where('unit_price', '<=', BackEndHelper::currency_to_aed($max_price));
                        } else if ($max_price == 'end') {
                            $query = $query->where('unit_price', '>=', BackEndHelper::currency_to_aed($min_price));
                        } else {
                            $query = $query->whereBetween('unit_price', [BackEndHelper::currency_to_aed($min_price),
                                BackEndHelper::currency_to_aed($max_price)]);
                        }
                        for ($i = 1; $i < sizeof($prices); $i++) {
                            $price = explode('-', $prices[$i]);
                            $min_price = $price[0];
                            $max_price = $price[1];
                            if ($min_price == 'end') {
                                $query = $query->orWhere('unit_price', '<=', BackEndHelper::currency_to_aed($max_price));
                            } else if ($max_price == 'end') {
                                $query = $query->orWhere('unit_price', '>=', BackEndHelper::currency_to_aed($min_price));
                            } else {
                                $query = $query->orWhereBetween('unit_price', [BackEndHelper::currency_to_aed($min_price),
                                    BackEndHelper::currency_to_aed($max_price)]);
                            }
                        }
                    }
                    return $query;
                });
            }

        }

        Log::info($products->toSql());

        return $products;
    }
}
