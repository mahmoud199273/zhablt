<?php

namespace Webkul\API\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\API\Http\Resources\Catalog\Product as ProductResource;
use Webkul\API\Http\Resources\Catalog\ProductM as ProductResourceM;

class WishlistM extends JsonResource
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
            'id'         => $this->id,
            'product'    => new ProductResourceM($this->product),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}