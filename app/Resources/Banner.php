<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Banner extends JsonResource
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
            'title' => $this->title,
            'photo' => asset('storage/banner') . '/' . $this->photo,
            'banner_type' => $this->banner_type,
            'url' => $this->url,
            'resource_type' => $this->resource_type,
            'resource_id' => $this->resource_id,
        ];
    }
}