<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Contracts\Slider as SliderContract;

class Slider extends Model implements SliderContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'sliders';

    protected $fillable = [
        'title',
        'path',
        'content',
        'channel_id',
        'locale'
    ];

    /**
     * Get image url for the category image.
     */
    public function image_url()
    {
        if (! $this->path) {
            return;
        }

        $link_image = Storage::url($this->path);
        return $link_image;
        
        //return Storage::url($this->path);
    }

    /**
     * Get image url for the category image.
     */
    public function getImageUrlAttribute()
    {
        $link_image = str_replace('storage','public/storage',$this->image_url()); 
        return $link_image;
    }
}