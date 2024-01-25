<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Coupon extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $order = null;
        if (auth('api')->check()) {
            $order = \App\Model\Order::where('customer_id', auth('api')->user()->id)->
            where('coupon_code', $this->code)->pluck('coupon_code');
        }

        return [
            'id' => $this->id,
            'coupon_type' => $this->coupon_type,
            'title' => $this->title,
            'code' => $this->code,
            'discount' => $this->discount,
            'expire_date' => $this->expire_date,
            'is_used' => $order ? true : false,
            'discount_type' => $this->discount_type
        ];
    }
}