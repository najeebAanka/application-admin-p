<?php

namespace App\Resources;

use App\CPU\ProductManager;
use Illuminate\Http\Resources\Json\JsonResource;

class ChoiceOptions extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $options = [];
        foreach ($this->options as $op) {
            array_push($options, trim($op));
        }

        $trans = \App\Model\Attribute::select(request()->header('lang') == 'en' ? 'attributes.name' : 'translations.value as name')->
        join('translations', 'translations.translationable_id', '=', 'attributes.id')->
        where('translations.translationable_type', 'App\Model\Attribute')->
        where('name', 'like', "{$this->title}")->orWhere('translations.value', 'like', "{$this->title}")->first();

        return [
            'name' => $this->name,
            'title' => $trans ? $trans->name : $this->title,
            'options' => $options,
        ];
    }
}