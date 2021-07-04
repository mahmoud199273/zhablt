<?php

namespace Webkul\API\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Helpers\ProductType;

class Home extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @return void
     */
    public function __construct($resource)
    {
        $this->productImageHelper = app('Webkul\Product\Helpers\ProductImage');

        $this->productReviewHelper = app('Webkul\Product\Helpers\Review');

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->product ? $this->product : $this;

        return [
            'id'                     => $product->id,
            'name'                   => $this->name,
            'price'                  => $product->getTypeInstance()->getMinimalPrice(),
            'formated_price'         => core()->currency($product->getTypeInstance()->getMinimalPrice()),
            'short_description'      => $this->short_description,
            'base_image'             => $this->productImageHelper->getProductBaseImage($product)['medium_image_url'],
            'special_price'          => $this->when(
                    $product->getTypeInstance()->haveSpecialPrice(),
                    $product->getTypeInstance()->getSpecialPrice()
                ),
            'formated_special_price' => $this->when(
                    $product->getTypeInstance()->haveSpecialPrice(),
                    core()->currency($product->getTypeInstance()->getSpecialPrice())
                ),
            
        ];
    }
}