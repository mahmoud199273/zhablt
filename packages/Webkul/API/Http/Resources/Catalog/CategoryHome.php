<?php

namespace Webkul\API\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Category\Models\Category as CategoryModal;

class CategoryHome extends JsonResource
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
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'status'           => $this->status,
            'image_url'        => $this->image_url,
            'products'        => $this->products,
           
        ];
    }
}