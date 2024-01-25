<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Address extends JsonResource
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
            'contact_person_name' => $this->contact_person_name,
            'address_type' => $this->address_type,
            'address' =>$this->address,
            'address2' =>$this->address2,
            'city' => $this->city,
            'zip' => $this->zip,
            'phone' => $this->phone,
            'state' => $this->state,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_billing' => $this->is_billing
        ];
    }
}