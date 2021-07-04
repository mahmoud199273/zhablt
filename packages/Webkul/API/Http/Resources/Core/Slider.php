<?php

namespace Webkul\API\Http\Resources\Core;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;
class Slider extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $pos = strrpos($this->slider_path, '/');
        $url_key = $pos === false ? $this->slider_path : substr($this->slider_path, $pos + 1);
        $query = DB::table('product_flat');
        $product = $query->where('url_key',$url_key)->get()->first();
        $product_id = ($product)?$product->product_id:null;
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            //'image_url'   => $this->image_url,
            'image_url'     => url($this->path),
            'content'       => $this->content,
            'slider_path'   => $this->slider_path,
            'product_id'    => $product_id,
        ];
    }
}