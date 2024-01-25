<?php

namespace App\Resources;

use App\CPU\ProductManager;
use Illuminate\Http\Resources\Json\JsonResource;

class Wishlist extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        //images
        $images = json_decode($this->images);
        foreach (json_decode($this->images) as $key => $img) {
            $images[$key] = asset('storage/product') . '/' . $img;
        }

        //reviews
        $reviews = $this->reviews;
        $overall_rating = ProductManager::get_overall_rating($reviews);

        $price = $this->discount > 0?
            \App\CPU\Helpers::currency_converter(
                $this->unit_price-(\App\CPU\Helpers::get_product_discount($this,$this->unit_price))
            ):\App\CPU\Helpers::currency_converter($this->unit_price);

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
            'details' => $this->details,
            'thumbnail' => asset('storage/product/thumbnail') . '/' . $this->thumbnail,
            'images' => $images,
            'price' => $price,
            'is_favourite' => $is_favourite,
            'in_stock' => $this->current_stock > 0 ? true : false,
            'rating' => [
                'overall_rating' => $overall_rating[0],
                'total_rating' => $overall_rating[1],
            ]
        ];
    }
}