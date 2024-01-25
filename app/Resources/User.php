<?php

namespace App\Resources;

use App\Model\CustomerWallet;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'name' => !is_null($this->name) ? $this->name : '',
            'f_name' => $this->f_name,
            'l_name' => $this->l_name,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,
            'points' => \App\Resources\CustomerWallet::make(CustomerWallet::where('customer_id', $this->id)->first()),
            'image' => asset('storage/profile') . '/' . $this->image
        ];
    }

}