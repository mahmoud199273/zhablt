<?php

namespace Webkul\API\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderAddressM extends JsonResource
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
            'id'           => $this->id,
            'email'        => $this->email,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'address1'     => $this->address1,
            'country'      => $this->country,
            'country_name' => core()->country_name($this->country),
            'state'        => $this->state,
            'city'         => $this->city,
            'postcode'     => $this->postcode,
            'phone'        => $this->phone,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}