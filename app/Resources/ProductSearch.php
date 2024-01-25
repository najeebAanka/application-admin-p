<?php

namespace App\Resources;

use App\CPU\ProductManager;
use function App\CPU\translate;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductSearch extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $categories = json_decode($this->category_ids);
        $category = null;
        if (sizeof($categories) > 0) {
            $category = \App\Model\Category::where('id', $categories[0]->id)->first();
            $category = $category ? $category->name : '';
        }
        $result = '';
        $result = str_replace($request->get('search_text'), '<b src="color:color: #3f51b5;">' . $request->get('search_text') . '</b>', $this->name);
        if (strlen($category) > 0) {
            $result = $result . ' ' . translate('in') . ' ' . ' <i src="color:color: #3f51b5;">' . $category . '</i>';
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $category ? $category : '',
            'result' => '<div>' . $result . '</div>'
        ];
    }
}