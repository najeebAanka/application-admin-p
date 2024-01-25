<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class Seller extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $review = DB::select("select avg(rating) as rate from products left join reviews on products.id=reviews.product_id 
where products.user_id={$this->id} group by products.user_id");


        return [
            'name' => !is_null($this->name) ? $this->name : '',
            'f_name' => $this->f_name,
            'l_name' => $this->l_name,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthdate' => '',
            'review' => round(sizeof($review)>0?$review[0]->rate:0,2),
            'image' => asset('storage/profile') . '/' . $this->image
        ];
    }

}