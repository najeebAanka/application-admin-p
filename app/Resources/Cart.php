<?php

namespace App\Resources;

use App\CPU\ProductManager;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Cart extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $product = \App\Model\Product::where('id', $this->product_id)->first();
        $available_quantity = 0;
        $vendor_name = '';
        if ($product) {
            $available_quantity = $product->current_stock;
            $vendor_name = $product->seller ? ($product->seller->f_name . ' ' . $product->seller->l_name) : '';
            $variations = json_decode($product->variation);
            foreach ($variations as $variation) {
                if ($variation->type == $this->variant) {
                    $available_quantity = $variation->qty;
                }
            }
        }

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'cart_group_id' => $this->cart_group_id,
            'product_id' => $this->product_id,
            'choices' => $this->choices,
            'variations' => $this->variations,
            'variant' => $this->variant,
            'available_quantity' => $available_quantity,
            'vendor_name' => $vendor_name,
            'quantity' => $this->quantity,
            'price' => \App\CPU\Helpers::currency_converter($this->price),
            'price_num' => $this->price,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'slug' => $this->slug,
            'name' => $this->name,
            'thumbnail' => asset('storage/product/thumbnail') . '/' . ($this->product ? $this->product->thumbnail : ''),
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString()
        ];
    }
}