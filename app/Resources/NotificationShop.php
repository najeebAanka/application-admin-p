<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationShop extends JsonResource
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
            'product' => AuctionProductBref::make($this->product),
            'lowest_ask_new' => $this->lowest_ask_new,
            'lowest_ask_new_formated' => \App\CPU\Helpers::currency_converter($this->lowest_ask_new),
            'lowest_ask_old' => $this->lowest_ask_old,
            'lowest_ask_old_formated' => \App\CPU\Helpers::currency_converter($this->lowest_ask_old),
            'highest_bid_new' => $this->highest_bid_new,
            'highest_bid_new_formated' => \App\CPU\Helpers::currency_converter($this->highest_bid_new),
            'highest_bid_old' => $this->highest_bid_old,
            'highest_bid_old_formated' => \App\CPU\Helpers::currency_converter($this->highest_bid_old)
        ];
    }
}