<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\API\Http\Resources\Checkout\Cart as CartResource;
use Webkul\API\Http\Resources\Checkout\CartM as CartResourceM;
use Cart;
use Webkul\Customer\Repositories\WishlistRepository;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class CartController extends Controller
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
     * WishlistRepository object
     *
     * @var \Webkul\Checkout\Repositories\WishlistRepository
     */
    protected $wishlistRepository;

    /**
     * Controller instance
     *
     * @param  \Webkul\Checkout\Repositories\CartRepository  $cartRepository
     * @param  \Webkul\Checkout\Repositories\CartItemRepository  $cartItemRepository
     * @param  \Webkul\Checkout\Repositories\WishlistRepository  $wishlistRepository
     */
    public function __construct(
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        WishlistRepository $wishlistRepository
    ) {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);

        // $this->middleware('auth:' . $this->guard);

        $this->_config = request('_config');

        $this->cartRepository = $cartRepository;

        $this->cartItemRepository = $cartItemRepository;

        $this->wishlistRepository = $wishlistRepository;
    }

    /**
     * Get customer cart
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        //$customer = auth($this->guard)->user();
        $customer =  $this->getAuthenticatedUser();

        $cart = Cart::getCart();

        return response()->json([
            'data' => $cart ? new CartResourceM($cart) : [],
            'message' => 'success',
            'status_code' => 200
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store($id=null)
    {

        $validator = Validator::make( request()->all(), [
            'product'   => 'required',
            'quantity'  => 'required',
        ]);

        if ($validator->fails()) {
            //return response()->json(['message' => $validator->messages()->first()], 422); 
            return $this->setStatusCode(422)->respondWithError($validator->messages()->first());
        }

        if (request()->get('is_buy_now')) {
            Event::dispatch('shop.item.buy-now', request()->product);
        }
        Event::dispatch('checkout.cart.item.add.before', request()->product);

        $result = Cart::addProduct(request()->product, request()->except('_token'));
        
        if (! $result) {
            $message = session()->get('warning') ?? session()->get('error');

            return response()->json([
                'error' => session()->get('warning'),
            ], 400);
        }
        $customer =  $this->getAuthenticatedUser();
        if ($customer) {
            $this->wishlistRepository->deleteWhere(['product_id' => request()->product, 'customer_id' => $customer->id]);
        }

        Event::dispatch('checkout.cart.item.add.after', $result);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message'   => __('shop::app.checkout.cart.item.success'),
            'status_code'    => 200,
            'data'      => $cart ? new CartResource($cart) : [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update_old()
    {
        if(!request()->get('qty')){
            return response()->json([
                'message' => "Parameter faild Validation",
                'status_code'   => 422,
            ], 422);
        }
        foreach (request()->get('qty') as $qty) {
            if ($qty <= 0) {
                return response()->json([
                    'message' => trans('shop::app.checkout.cart.quantity.illegal'),
                    'status_code'   => 401,
                ], 401);
            }
        }

        foreach (request()->get('qty') as $itemId => $qty) {
            $item = $this->cartItemRepository->findOneByField('id', $itemId);

            Event::dispatch('checkout.cart.item.update.before', $itemId);

            Cart::updateItems(request()->all());

            Event::dispatch('checkout.cart.item.update.after', $item);
        }

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message' => __('shop::app.checkout.cart.quantity.success'),
            'status_code'   => 200,
            'data'    => $cart ? new CartResource($cart) : null,
        ]);
    }

    public function update()
    {
        if(!request()->qty || !request()->cart_item_id){
            return response()->json([
                        'message' => "Parameter faild Validation",
                        'status_code'   => 422,
                    ], 422);
        }
        
        if (request()->qty <= 0) {
            return response()->json([
                    'message' => trans('shop::app.checkout.cart.quantity.illegal'),
                    'status_code'   => 401,
            ], 401);
        }

        //foreach (request()->get('qty') as $itemId => $qty) {
            $item = $this->cartItemRepository->findOneByField('id', request()->cart_item_id);

            Event::dispatch('checkout.cart.item.update.before', request()->cart_item_id);

            Cart::updateApiItems(request()->all());

            Event::dispatch('checkout.cart.item.update.after', $item);
        //}

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message' => __('shop::app.checkout.cart.quantity.success'),
            'status_code'   => 200,
            'data'    => $cart ? new CartResourceM($cart) : null,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        Event::dispatch('checkout.cart.delete.before');

        Cart::deActivateCart();

        Event::dispatch('checkout.cart.delete.after');

        $cart = Cart::getCart();

        return response()->json([
            'message' => __('shop::app.checkout.cart.item.success-remove'),
            'status_code'   => 200,
            'data'    => $cart ? new CartResource($cart) : null,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyItem($id)
    {
        Event::dispatch('checkout.cart.item.delete.before', $id);

        Cart::removeItem($id);

        Event::dispatch('checkout.cart.item.delete.after', $id);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message' => __('shop::app.checkout.cart.item.success-remove'),
            'status_code'   => 200,
            'data'    => $cart ? new CartResource($cart) : null,
        ]);
    }

    /**
     * Function to move a already added product to wishlist will run only on customer authentication.
     *
     * @param  \Webkul\Checkout\Repositories\CartItemRepository  $id
     */
    public function moveToWishlist($id)
    {
        Event::dispatch('checkout.cart.item.move-to-wishlist.before', $id);

        Cart::moveToWishlist($id);

        Event::dispatch('checkout.cart.item.move-to-wishlist.after', $id);

        Cart::collectTotals();

        $cart = Cart::getCart();

        return response()->json([
            'message' => __('shop::app.checkout.cart.move-to-wishlist-success'),
            'status_code'   => 200,
            'data'    => $cart ? new CartResource($cart) : null,
        ]);
    }
}