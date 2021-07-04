<?php

namespace Webkul\API\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;

class CustomerAddressNew extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        
        $city = DB::table('country_states')->select('country_states.default_name as en_locale','country_state_translations.default_name as ar_locale')->join('country_state_translations','country_state_translations.country_state_id', '=', 'country_states.id')->where('country_states.id',$this->city)->first();
        $country = DB::table('countries')->select('countries.name as en_locale','country_translations.name as ar_locale')->join('country_translations','country_translations.country_id', '=', 'countries.id')
         ->where('countries.id',$this->country)->first();
        $country_name = ($country)?$country->en_locale:"";
        $city_name = ($city)?$city->en_locale:"";
        if(app()->getLocale() == "ar")
        {
            $country_name = ($country)?$country->ar_locale:"";
            $city_name = ($city)?$city->ar_locale:"";
        }
        return [
            'id'           => $this->id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'company_name' => $this->company_name,
            //'address1'     => explode(PHP_EOL, $this->address1),
            'address1'     => $this->address1,
            'country'      => $this->country,
            //'country_name' => core()->country_name($this->country),
            'country_name' => $country_name,
            'state'        => $this->state,
            'city'         => $this->city,
            'city_name'         => $city_name,
            'default_address'         => $this->default_address,
            'postcode'     => $this->postcode,
            'phone'        => $this->phone,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
