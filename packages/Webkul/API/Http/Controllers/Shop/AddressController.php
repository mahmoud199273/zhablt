<?php

namespace Webkul\API\Http\Controllers\Shop;

use Webkul\Customer\Repositories\CustomerAddressRepository;
use Webkul\API\Http\Resources\Customer\CustomerAddress as CustomerAddressResourceOld;
use Webkul\API\Http\Resources\Customer\CustomerAddressNew as CustomerAddressResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Webkul\Customer\Contracts\CustomerAddress;
use DB;

class AddressController extends Controller
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * CustomerAddressRepository object
     *
     * @var \Webkul\Customer\Repositories\CustomerAddressRepository
     */
    protected $customerAddressRepository;

    /**
     * Controller instance
     *
     * @param  Webkul\Customer\Repositories\CustomerAddressRepository  $customerAddressRepository
     */
    public function __construct(CustomerAddressRepository $customerAddressRepository)
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);

        $this->middleware('auth:' . $this->guard);

        $this->_config = request('_config');

        $this->customerAddressRepository = $customerAddressRepository;
    }

    /**
     * Get user address.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        //$customer = auth($this->guard)->user();
        $customer =  $this->getAuthenticatedUser();
        
        $addresses = $customer->addresses()->get();

        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'data'          => CustomerAddressResource::collection($addresses),
        ]);
        //return CustomerAddressResource::collection($addresses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //$customer = auth($this->guard)->user();
        $customer =  $this->getAuthenticatedUser();

        request()->merge([
            //'address1'    => implode(PHP_EOL, array_filter(request()->input('address1'))),
            'customer_id' => $customer->id,
        ]);
        $validator = Validator::make( request()->all(), [
                'address1' => 'string|required',
                'country'  => 'required',
                'city'     => 'required',
                'postcode' => 'required',
                'phone'    => 'required',
        ]);

        if ($validator->fails()) {
            return $this->setStatusCode(422)->respondWithError($validator->messages()->first());
        }

        // request()->merge([
        //     'customer_id' => $customer->id,
        // ]);

       
        $customerAddress = $this->customerAddressRepository->create(request()->all());

        return response()->json([
            'message'       => 'Your address has been created successfully.',
            'status_code'   => 200,
            'data'          => new CustomerAddressResource($customerAddress),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //$customer = auth($this->guard)->user();
        $customer =  $this->getAuthenticatedUser();

        request()->merge([
            'customer_id' => $customer->id,
        ]);

        $validator = Validator::make( request()->all(), [
            'address1' => 'string|required',
            'country'  => 'required',
            'city'     => 'required',
            'postcode' => 'required',
            'phone'    => 'required',
        ]);

        if ($validator->fails()) {
            //return response()->json(['message' => $validator->messages()->first()], 422); 
            return $this->setStatusCode(422)->respondWithError($validator->messages()->first());
        }

        $address = DB::table('addresses')->where(['customer_id' => $customer->id, 'id' => $id])->first();
        if(!$address)
        {
            return response()->json([
                'message'           => 'Address not exists.',
                'status_code'       => 406,
            ]);
        }
       

            $this->customerAddressRepository->update(request()->all(),$id);
            return response()->json([
                'message'           => 'Your address has been updated successfully.',
                'status_code'       => 200,
                'data'              => new CustomerAddressResource($this->customerAddressRepository->find($id)),
            ]);
        
        
    }
}