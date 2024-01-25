<?php

namespace App\Resources;

use App\CPU\CategoryManager;
use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {


        $products = CategoryManager::products_paginate($this->id, 10, 1);
        $products['products'] = ProductBref::collection($products['products']);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => asset('storage/category') . '/' . $this->icon,
            'banner' => asset('storage/category') . '/' . $this->banner,
            'parent_id' => $this->parent_id,
            'position' => $this->position,
            'products_style' => (int)$this->products_style,
            'is_gift' => (int)$this->is_gift,
            'childes' => Category::collection($this->childes),
//            'products' => ($this->parent && (int)$this->parent->is_gift) ? $products['products'] : []
            'products' => $products['products']
        ];
    }
}