<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class Kitchen extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {


        $image = json_decode($this->images);
        if($image!=null){
            foreach ($image as $key => $img) {
                $image[$key] = Storage::disk('public')->exists('kitchen/' . $img) ?
                    asset('storage/kitchen') . '/' . $img
                    : asset('assets/front-end/img/image-place-holder.png');
            }
        }else{
            $image = [];
        }

        return [
            'id' => $this->id,
            'service_on' => $this->service_on,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'location' => $this->location,
            'kitchen_type' => $this->kitchen_type,
            'floor_type' => $this->floor_type,
            'surface_type' => $this->surface_type,
            'kitchen_color' => $this->kitchen_color,
            'additional_color' => $this->additional_color,
            'length' => $this->length	,
            'width' => $this->width,
            'height' => $this->height,
            'images' => $image
        ];
    }
}