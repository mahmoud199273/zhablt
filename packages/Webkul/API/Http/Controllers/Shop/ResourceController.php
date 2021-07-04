<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;

class ResourceController extends Controller
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
     * Repository object
     *
     * @var \Webkul\Core\Eloquent\Repository
     */
    protected $repository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        $this->_config = request('_config');

        if (isset($this->_config['authorization_required']) && $this->_config['authorization_required']) {

            auth()->setDefaultDriver($this->guard);

            $this->middleware('auth:' . $this->guard);
        }

        $this->repository = app($this->_config['repository']);
    }


    /**
     * Returns static pages data.
     *
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if(strpos(request()->server("REQUEST_URI"), '/invoices'))
        {
            if(!request()->order_id)
            {
                return response()->json([
                    'message'           => 'Order id is required',
                    'status_code'       => 406,
                ]);
            }
        }

        $query = $this->repository->scopeQuery(function($query) {
            foreach (request()->except(['page', 'limit', 'pagination', 'sort', 'order', 'token']) as $input => $value) {
                $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
            }

            if ($sort = request()->input('sort')) {
                $query = $query->orderBy($sort, request()->input('order') ?? 'desc');
            } else {
                $query = $query->orderBy('id', 'desc');
            }

            return $query;
        });
        if(strpos(request()->server("REQUEST_URI"), '/orders'))
        {
            $customer =  $this->getAuthenticatedUser();
            $query = $query->where("customer_id",$customer->id);
        }    


        if(strpos(request()->server("REQUEST_URI"), '/invoices'))
        {
            $query = $query->where("order_id",request()->order_id);
        }    


        
        if(strpos(request()->server("REQUEST_URI"), '/categories'))
        {
            $query = $query->whereNull("parent_id");
        }    
        

        if ( request()->limit ) {
            $this->setPagination(request()->limit);
        }

        if(strpos(request()->server("REQUEST_URI"), '/sliders') || strpos(request()->server("REQUEST_URI"), '/countries'))
        {
            $results = $query->get();
            return response()->json([
                'status_code'=>200,
                'message' => 'success',
                'data'    => $this->_config['resource']::collection($results),
            ]);
            
        }
        else
        {
            $results = $query->paginate(request()->input('limit') ?? 10);
            return $this->respondWithPagination($results, ['data' => $this->_config['resource']::collection($results),'status_code' => 200 , 'message' => "success" ]);
        }
        //if (is_null(request()->input('pagination')) || request()->input('pagination')) {
            
        // } else {
        //     $results = $query->get();
        // }
        

        
        
        
    }


    public function HomeCategories()
    {
        $query = $this->repository->scopeQuery(function($query) {
            foreach (request()->except(['page', 'limit', 'pagination', 'sort', 'order', 'token']) as $input => $value) {
                $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
            }

            if ($sort = request()->input('sort')) {
                $query = $query->orderBy($sort, request()->input('order') ?? 'desc');
            } else {
                $query = $query->orderBy('id', 'desc');
            }

            return $query;
        });

        
        $results = $query->limit(3)->get();
        
        return response()->json([
            'status_code'=>200,
            'message' => 'success',
            'data'    => $this->_config['resource']::collection($results),
        ]);
        
    }

    /**
     * Returns a individual resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        return response()->json([
            'status_code'=>200,
            'message' => 'success',
            'data'    => new $this->_config['resource'](
                $this->repository->findOrFail($id)
            ),
        ]);
       
    }

    /**
     * Delete's a individual resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wishlistProduct = $this->repository->findOrFail($id);

        $this->repository->delete($id);
        
        return response()->json([
            'message' => 'Item removed successfully.',
            'status_code' => 200,
        ]);
    }
}
