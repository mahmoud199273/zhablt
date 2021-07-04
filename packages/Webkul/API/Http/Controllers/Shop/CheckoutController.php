<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Payment\Facades\Payment;
use Webkul\API\Http\Resources\Checkout\Cart as CartResource;
use Webkul\API\Http\Resources\Checkout\CartShippingRate as CartShippingRateResource;
use Webkul\API\Http\Resources\Sales\Order as OrderResource;
use Webkul\Checkout\Http\Requests\CustomerAddressForm;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Str;
use Cart;

class CheckoutController extends Controller
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * CartRepository object
     *
     * @var \Webkul\Checkout\Repositories\CartRepository
     */
    protected $cartRepository;

    /**
     * CartItemRepository object
     *
     * @var \Webkul\Checkout\Repositories\CartItemRepository
     */
    protected $cartItemRepository;

    /**
     * Controller instance
     *
     * @param  \Webkul\Checkout\Repositories\CartRepository  $cartRepository
     * @param  \Webkul\Checkout\Repositories\CartItemRepository  $cartItemRepository
     * @param  \Webkul\Sales\Repositories\OrderRepository  $orderRepository
     */
    public function __construct(
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        OrderRepository $orderRepository
    )
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);

        // $this->middleware('auth:' . $this->guard);

        $this->_config = request('_config');

        $this->cartRepository = $cartRepository;

        $this->cartItemRepository = $cartItemRepository;

        $this->orderRepository = $orderRepository;
    }

    /**
     * Saves customer address.
     *
     * @param  \Webkul\Checkout\Http\Requests\CustomerAddressForm $request
     * @return \Illuminate\Http\Response
    */
    //public function saveAddress(CustomerAddressForm $request)
    public function saveAddress()
    {
        //$obj = new CustomerAddressForm();
        $data = request()->all();

        //$data['billing']['address1'] = implode(PHP_EOL, array_filter($data['billing']['address1']));

        //$data['shipping']['address1'] = implode(PHP_EOL, array_filter($data['shipping']['address1']));

        //$data['billing']['address1'] = $data['billing']['address1'];

        //$data['shipping']['address1'] = $data['shipping']['address1'];

        if (isset($data['billing']['id']) && str_contains($data['billing']['id'], 'address_')) {
            unset($data['billing']['id']);
            unset($data['billing']['address_id']);
        }

        if (isset($data['shipping']['id']) && Str::contains($data['shipping']['id'], 'address_')) {
            unset($data['shipping']['id']);
            unset($data['shipping']['address_id']);
        }


        if (Cart::hasError() || ! Cart::saveCustomerAddress($data) || ! Shipping::collectRates()) {
            return response()->json([
                'message'       => 'Something went wrong',
                'status_code'   => 400,
            ]);
        }

        $rates = [];

        foreach (Shipping::getGroupedAllShippingRates() as $code => $shippingMethod) {
            $rates[] = [
                'carrier_title' => $shippingMethod['carrier_title'],
                'rates'         => CartShippingRateResource::collection(collect($shippingMethod['rates'])),
            ];
        }

        Cart::collectTotals();

        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'data' => [
                'rates' => $rates,
                'cart'  => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Saves shipping method.
     *
     * @return \Illuminate\Http\Response
    */
    public function saveShipping()
    {
        $shippingMethod = (request()->get('shipping_method'))?request()->get('shipping_method'):"flatrate_flatrate";

        if (Cart::hasError()
            || !$shippingMethod
            || ! Cart::saveShippingMethod($shippingMethod)
        ) {
            abort(400);
        }

        Cart::collectTotals();

        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'data' => [
                'methods' => Payment::getPaymentMethods(),
                'cart'    => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Saves payment method.
     *
     * @return \Illuminate\Http\Response
    */
    public function savePayment()
    {
        $payment = request()->get('payment');
        if(!$payment)
        {
            return response()->json([
                'message'       => 'Payment Method is required',
                'status_code'   => 422,
            ]);
        }
        if (Cart::hasError() || ! $payment || ! Cart::savePaymentMethodM($payment)) {
            return response()->json([
                'message'       => 'Something went wrong',
                'status_code'   => 400,
            ]);
        }

        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'data' => [
                'cart' => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Saves order.
     *
     * @return \Illuminate\Http\Response
    */
    public function saveOrder()
    {
        $this->saveAddress();
        $this->savePayment();
        $this->saveShipping();
        if (Cart::hasError()) {
            abort(400);
        }

        Cart::collectTotals();

        $this->validateOrder();

        $cart = Cart::getCart();

        if ($redirectUrl = Payment::getRedirectUrl($cart)) {
            return response()->json([
                    'success'      => true,
                    'redirect_url' => $redirectUrl,
                ]);
        }

        $order = $this->orderRepository->create(Cart::prepareDataForOrder());

        Cart::deActivateCart();

        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'success' => true,
            'order'   => new OrderResource($order),
        ]);
    }

    /**
     * Validate order before creation
     *
     * @return mixed
     */
    public function validateOrder()
    {
        
        $cart = Cart::getCart();
        
        if (! $cart->shipping_address) {
            return response()->json([
                'status_code'   => 401,
                'message' => trans('Please check shipping address.'),
            ], 401);
            //throw new \Exception(trans('Please check shipping address.'));
        }

        if (! $cart->billing_address) {
            return response()->json([
                'status_code'   => 401,
                'message' => trans('Please check billing address.'),
            ], 401);
            //throw new \Exception(trans('Please check billing address.'));
        }

        if (! $cart->selected_shipping_rate) {
            return response()->json([
                'status_code'   => 401,
                'message' => trans('Please check shipping method.'),
            ], 401);
            //dd("selected_shipping_rate");
           // throw new \Exception(trans('Please specify shipping method.'));
        }

        if (! $cart->payment) {
            return response()->json([
                'status_code'   => 401,
                'message' => trans('Please check payment method.'),
            ], 401);
            //throw new \Exception(trans('Please specify payment method.'));
        }
    }
}