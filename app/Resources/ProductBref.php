<?php

namespace App\Resources;

use App\CPU\ProductManager;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductBref extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return \Cache::remember('ProductBref' . $this->slug, 2, function () {
            //images
            $images = json_decode($this->images);
            foreach (json_decode($this->images) as $key => $img) {
                $images[$key] =  Storage::disk('public')->exists('product/' . $img) ?
                    asset('storage/product') . '/' . $img
                    : asset('assets/front-end/img/image-place-holder.png');
            }

            //reviews
            $reviews = $this->reviews;
            $overall_rating = ProductManager::get_overall_rating($reviews);

            $price = $this->unit_price;
            $offer_price = $this->discount > 0 ?
                $this->unit_price - (\App\CPU\Helpers::get_product_discount($this, $this->unit_price)) : $this->unit_price;


            $is_favourite = false;
            if (auth('api')->check()) {
                $is_favourite = \App\Model\Wishlist::where([
                    'product_id' => $this->id,
                    'customer_id' => auth('api')->user()->id,
                ])->first() ? true : false;
            }

            return [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'share_link' => route('product', ['slug' => $this->slug]),
                'details' => $this->details,
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
                'in_stock' => $this->current_stock > 0 ? true : false,
                'rating' => [
                    'overall_rating' => $overall_rating[0],
                    'total_rating' => $overall_rating[1],
                ]
            ];
        });
    }
}