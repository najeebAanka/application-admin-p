<?php

namespace App\Resources;

use App\CPU\ProductManager;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class Product extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        return \Cache::remember('Product' . $this->slug, 2, function () {
            //images
            $images = json_decode($this->images);
            foreach (json_decode($this->images) as $key => $img) {
                $images[$key] = Storage::disk('public')->exists('product/' . $img) ?
                    asset('storage/product') . '/' . $img
                    : asset('assets/front-end/img/image-place-holder.png');
            }

            //reviews
            $reviews = $this->reviews;
            $overall_rating = ProductManager::get_overall_rating($reviews);


            $is_favourite = false;
            $fav_seller = false;
            if (auth('api')->check()) {
                $is_favourite = \App\Model\Wishlist::where([
                    'product_id' => $this->id,
                    'customer_id' => auth('api')->user()->id,
                ])->first() ? true : false;

                $fav_seller = \App\Model\FavSeller::where('user_id', auth('api')->user()->id)
                    ->where('seller_id', $this->seller ? $this->seller->id : '')->first() ? true : false;
            }

//
//            $attributes = [];
//            if (json_decode($this->attributes) != null) {
//                foreach (json_decode($this->attributes) as $attribute) {
//                    array_push($attributes, (integer)$attribute);
//                }
//            }

            $choice_options = json_decode($this->choice_options);
            $variation = [];
            foreach (json_decode($this->variation, true) as $var) {

                $v_price = round((double)$var['price'], 2);
                $v_offer_price = $this->discount > 0 ?
                    $v_price - (\App\CPU\Helpers::get_product_discount($this, $v_price)) : $v_price;
                array_push($variation, [
                    'type' => $var['type'],
                    'price' => $v_price,
                    'price_formated' => \App\CPU\Helpers::currency_converter($v_price),
                    'offer_price' => $v_offer_price,
                    'offer_price_formated' => \App\CPU\Helpers::currency_converter($v_offer_price),
                    'sku' => $var['sku'],
                    'qty' => (int)$var['qty'],
                ]);
            }

            $related_products = ProductManager::get_related_products($this->id);
            $related_products = ProductBref::collection($related_products);

            $price = $this->unit_price;
            $offer_price = $this->discount > 0 ?
                $this->unit_price - (\App\CPU\Helpers::get_product_discount($this, $this->unit_price)) : $this->unit_price;


            $tax = $this->tax > 0 ? ($this->tax / 100) * $price : 0;

            $categories = json_decode($this->category_ids);
            $cats = [];
            if (is_array($categories)) {
                foreach ($categories as $category) {
                    $category = \App\Model\Category::where('id', $category->id)->first();
                    if ($category) {
                        array_push($cats, [
                            'id' => $category->id,
                            'name' => $category->name
                        ]);
                    }
                }
            }

            $similar_products = $related_products;
            $get_reviews = \App\Model\Review::where('product_id', $this->id)->orderBy('id', 'desc')->skip(0)->take(5)->get();

            return [
                'id' => $this->id,
                'name' => $this->name,
                'details' => $this->details,
                'description' => $this->details_description,
                'model' => $this->model,
                'features' => $this->features,
                'slug' => $this->slug,
                'categories' => $cats,
                'colors' => !is_array($this->colors) ? json_decode($this->colors) : $this->colors,
                'share_link' => route('product', ['slug' => $this->slug]),
                'thumbnail' =>
                    Storage::disk('public')->exists('product/thumbnail/' . $this->thumbnail) ?
                        asset('storage/product/thumbnail') . '/' . $this->thumbnail
                        : asset('assets/front-end/img/image-place-holder.png')
                ,
                'images' => $images,
                'price' => $price,
                'price_formatted' => \App\CPU\Helpers::currency_converter($price),
                'offer_price' => $offer_price,
                'offer_price_formatted' => \App\CPU\Helpers::currency_converter($offer_price),
                'is_favourite' => $is_favourite,
//                'attributes' => $attributes,
                'in_stock' => $this->current_stock > 0 ? true : false,
                'rating' => [
                    'overall_rating' => $overall_rating[0],
                    'total_rating' => $overall_rating[1],
                ],
                'variation' => $variation,
                'choice_options' => ChoiceOptions::collection($choice_options),
                'brand' => $this->brand ? Brand::make($this->brand) : null,
                'related_products' => $related_products,
                'similar_products' => $similar_products,
                'has_discount' => ($this->discount > 0),
                'has_tax' => ($this->tax > 0),
                'tax' => \App\CPU\Helpers::currency_converter($tax),
                'unit_price' => \App\CPU\Helpers::currency_converter($this->unit_price),
                'current_stock' => $this->current_stock,
                'reviews_count' => $this->reviews_count,
                'seller_id' => $this->seller ? $this->seller->id : '',
                'seller' => $this->seller ? Seller::make($this->seller) : null,
                'is_fav_seller' => $fav_seller,
                'reviews' => Review::collection($get_reviews),
            ];
        });
    }
}