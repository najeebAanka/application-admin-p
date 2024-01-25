<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetails extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_details' => ProductBref::make(json_decode($this->product_details)),
            'qty' => $this->qty,
            'price' => \App\CPU\Helpers::currency_converter($this->price),
            'tax' => $this->tax,
            'discount' => $this->discount,
            'delivery_status' => $this->delivery_status,
            'payment_status' => $this->payment_status,
            'shipping_method_id' => $this->shipping_method_id,
            'variant' => $this->variant,
//            'variation' => json_decode($this->variation),
            'discount_type' => $this->discount_type,
            'is_stock_decreased' => $this->is_stock_decreased
        ];
    }
}