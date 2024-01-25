<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class FAQ extends JsonResource
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
            'question' => $this->{'question' . (App::getLocale() == 'ae' ? '_ar' : '')} ?
                $this->{'question' . (App::getLocale() == 'ae' ? '_ar' : '')} : $this->{'question'},
            'answer' => $this->{'answer' . (App::getLocale() == 'ae' ? '_ar' : '')} ?
                $this->{'answer' . (App::getLocale() == 'ae' ? '_ar' : '')} : $this->{'answer'},
            'ranking' => $this->ranking,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'id' => $this->id
        ];
    }
}