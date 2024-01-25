<?php

namespace App\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
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
            'customer_id' => $this->customer_id,
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status,
            'payment_method' => $this->payment_method,
            'transaction_ref' => $this->transaction_ref,
            'order_amount' => $this->order_amount,
            'shipping_address' => $this->shipping_address,
            'shipping_address_data' => $this->shipping_address_data,
            'billing_address' => $this->shipping_address,
            'billing_address_data' => $this->billing_address_data,
            'discount_amount' => $this->discount_amount,
            'discount_type' => $this->discount_type,
            'coupon_code' => $this->coupon_code,
            'shipping_method_id' => $this->shipping_method_id,
            'shipping_cost' => $this->shipping_cost,
            'order_group_id' => $this->order_group_id,
            'verification_code' => $this->verification_code,
            'order_note' => $this->order_note,
            'seller_id' => $this->seller_is == 'admin' ? 'Apparels' : 'Shop (Buy Now)',
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'details'=>OrderDetails::collection($this->details)
        ];
    }
}