<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Support\Facades\Event;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;

class CustomerController extends Controller
{
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Repository object
     *
     * @var \Webkul\Customer\Repositories\CustomerRepository
     */
    protected $customerRepository;

    /**
     * Repository object
     *
     * @var \Webkul\Customer\Repositories\CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Customer\Repositories\CustomerRepository  $customerRepository
     * @param  \Webkul\Customer\Repositories\CustomerGroupRepository  $customerGroupRepository
     * @return void
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerGroupRepository $customerGroupRepository
    )   {
        $this->_config = request('_config');

        $this->customerRepository = $customerRepository;

        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * Method to store user's sign up form data to DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $validator = Validator::make( request()->all(), [
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'email|required|unique:customers,email',
            //'phone'      => 'email|required|unique:customers,email',
            'password'   => 'confirmed|min:6|required',
        ]);

        if ($validator->fails()) {
            //return response()->json(['message' => $validator->messages()->first()], 422); 
            return $this->setStatusCode(422)->respondWithError($validator->messages()->first());
        }
        // request()->validate([
        //     'first_name' => 'required',
        //     'last_name'  => 'required',
        //     'email'      => 'email|required|unique:customers,email',
        //     'password'   => 'confirmed|min:6|required',
        // ]);

        $data = request()->input();

        $data = array_merge($data, [
                'password'    => bcrypt($data['password']),
                'channel_id'  => core()->getCurrentChannel()->id,
                'is_verified' => 1,
            ]);

        $data['customer_group_id'] = $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id;

        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create($data);

        $token = JWTAuth::fromUser($customer);

        Event::dispatch('customer.registration.after', $customer);

        return response()->json([
            'status_code' => 200,
            'token'   => $token,
            'data'    => new CustomerResource($customer),
            'message' => 'Your account has been created successfully.',
        ]);
    }
}