<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class Review extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $attachment = json_decode($this->attachment);
        if($attachment!=null){
            foreach ($attachment as $key => $img) {
                $attachment[$key] = Storage::disk('public')->exists('review/' . $img) ?
                    asset('storage/review') . '/' . $img
                    : asset('assets/front-end/img/image-place-holder.png');
            }
        }else{
            $attachment = [];
        }

        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'attachment' => $attachment,
            'created_at' => $this->created_at,
            'customer' => [
                'id' => $this->customer_id,
                'name' => $this->customer ? $this->customer->name : '',
                'phone' => $this->customer ? $this->customer->phone : '',
                'image' => (asset('storage/profile') . '/' . $this->customer->image),
                'email' => $this->customer ? $this->customer->email : '',
            ]
        ];
    }
}