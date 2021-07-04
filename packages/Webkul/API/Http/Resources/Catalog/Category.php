<?php

namespace Webkul\API\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Category\Models\Category as CategoryModal;

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
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'display_mode'     => $this->display_mode,
            'description'      => $this->description,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords'    => $this->meta_keywords,
            'status'           => $this->status,
            'image_url'        => $this->image_url,
            //'sub_cats'         => CategoryModal::where('parent_id',$this->id)->get(),
            'sub_cats'         => $this->SubCats($this->id),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }

    public function SubCats($id)
    {
        $return = [];
        $locale = request()->get('locale') ?: app()->getLocale();
        $cat = CategoryModal::where('id',$id)->first();
        $return[] = [
            'id'               => $id,
            'code'             => $cat->code,
            'name'             => ($locale == "ar") ? "الكل":"all",
            'slug'             => $cat->slug,
            'display_mode'     => $cat->display_mode,
            'description'      => $cat->description,
            'meta_title'       => $cat->meta_title,
            'meta_description' => $cat->meta_description,
            'meta_keywords'    => $cat->meta_keywords,
            'status'           => $cat->status,
            'image_url'        => $cat->image_url,
            'created_at'       => $cat->created_at,
            'updated_at'       => $cat->updated_at,
        ];
        $data = CategoryModal::where('parent_id',$id)->get();
        foreach($data as $key => $value){
            $return[] = [
                'id'               => $this->id,
                'code'             => $this->code,
                'name'             => $this->name,
                'slug'             => $this->slug,
                'display_mode'     => $this->display_mode,
                'description'      => $this->description,
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords'    => $this->meta_keywords,
                'status'           => $this->status,
                'image_url'        => $this->image_url,
                'created_at'       => $this->created_at,
                'updated_at'       => $this->updated_at,
            ];
        }

        return $return;
    }
}