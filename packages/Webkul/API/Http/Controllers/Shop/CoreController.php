<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CoreController extends Controller
{


    
    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getConfig()
    {
        $configValues = [];

        foreach (explode(',', request()->input('_config')) as $config) {
            $configValues[$config] = core()->getConfigData($config);
        }
        
        return response()->json([
            'data' => $configValues,
        ]);
    }

    public function Pages()
    {
        
        $locale = request()->get('locale') ?: app()->getLocale();
        $query = DB::table('cms_pages')
        ->select('cms_pages.id', 'cms_page_translations.page_title', 'cms_page_translations.url_key','cms_page_translations.html_content')
        ->leftJoin('cms_page_translations', function($leftJoin) use ($locale ) {
            $leftJoin->on('cms_pages.id', '=', 'cms_page_translations.cms_page_id');
        })->where('cms_page_translations.locale', $locale )->where('cms_page_translations.url_key',request()->route()->getName())->get()->first();
        return response()->json([
            'data' => $query,
            'message' => "success",
            'status_code' => 200,
        ]);
    }



   

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountryStateGroup()
    {
        $locale = request()->get('locale') ?: app()->getLocale();
        $query = DB::table('country_states');
        if($locale == "ar")
        {
            $query = $query->select('country_states.id','country_states.country_code','country_states.code','country_states.country_id','country_state_translations.default_name' )
            ->leftJoin('country_state_translations', function($leftJoin) use ($locale ) {
                $leftJoin->on('country_state_translations.country_state_id', '=', 'country_states.id')
                         ->where('country_state_translations.locale', $locale );
            });
        }
        if(request()->get('country_id'))
        {
            $query = $query->where('country_id',request()->get('country_id'));
        }
        $result = $query->get();
        return response()->json([
            'message' => "success",
            'status_code' => 200,
            'data' => $result,
        ]);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function switchCurrency()
    {
        return response()->json([]);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function switchLocale()
    {
        return response()->json([]);
    }
}
