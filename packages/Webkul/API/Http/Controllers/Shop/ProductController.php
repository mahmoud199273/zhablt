<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\API\Http\Resources\Catalog\Product as ProductResourceOld;
use Webkul\API\Http\Resources\Catalog\ProductM as ProductResource;
use Webkul\API\Http\Resources\Catalog\Home as HomeResource;
use Webkul\Product\Models\Product;

use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class ProductController extends Controller
{
    /**
     * ProductRepository object
     *
     * @var \Webkul\Product\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Product\Repositories\ProductRepository $productRepository
     * @return void
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;

    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $results = $this->productRepository->getAllM(request()->input('category_id'));
        $data = ProductResource::collection($results);
        return $this->respondWithPagination($results, ['data' => $data,'status_code' => 200 , 'message' => "success" ]);
    }

    /**
     * Returns a individual resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        
        $product = $this->productRepository->findOrFail($id);
        $similar_products = [];
        if(isset($product->categories) && isset($product->categories[0]))
        {
            $cat_id = $product->categories[0]->id;
            $products = Product::where('id','!=',$product->id)->whereHas('categories', function ($query) use($cat_id) {
                return $query->where('category_id', $cat_id);
            })->limit(4)->get();
            $similar_products = ProductResource::collection($products);
        }
        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'similar_products'   => $similar_products,
            'data'          =>new ProductResource(
                    $this->productRepository->findOrFail($id)
                ),
        ]);
        // return new ProductResource(
        //     $this->productRepository->findOrFail($id)
        // );
    }

    /**
     * Returns product's additional information.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function additionalInformation($id)
    {
        return response()->json([
            'status_code' => 200,
            'message' => 'Success',
            'data' => app('Webkul\Product\Helpers\View')->getAdditionalData($this->productRepository->findOrFail($id)),
        ]);
    }

    /**
     * Returns product's additional information.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function configurableConfig($id)
    {
        return response()->json([
            'status_code' => 200,
            'message' => 'Success',
            'data' => app('Webkul\Product\Helpers\ConfigurableOption')->getConfigurationConfig($this->productRepository->findOrFail($id)),
        ]);
    }
}
