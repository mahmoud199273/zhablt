<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Models\Product;
use Webkul\Category\Models\Category;
use Webkul\API\Http\Resources\Catalog\Category as CategoryResource;
use Webkul\API\Http\Resources\Catalog\CategoryHome as CategoryHomeResource;
use Webkul\API\Http\Resources\Catalog\Home as ProductResourceOLD;
use Webkul\API\Http\Resources\Catalog\ProductM as ProductResource;
use DB;

class CategoryController extends Controller
{
    /**
     * CategoryRepository object
     *
     * @var \Webkul\Category\Repositories\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param  Webkul\Category\Repositories\CategoryRepository  $categoryRepository
     * @return void
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parent_id = (request()->input('parent_id'))?request()->input('parent_id'):3;
        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'data'          => CategoryResource::collection(
                $this->categoryRepository->getVisibleCategoryTree($parent_id)
            ),
        ]);
    }


    public function products()
    {
        
        $results = Category::get();
        foreach($results as $key=>$value)
        {
            $products = Product::whereHas('categories', function ($query) use($value) {
                return $query->where('category_id', $value->id);
            })->limit(10)->get();
            $results[$key]['products'] = ProductResource::collection($products);
            //$results[$key] = CategoryHomeResource::collection($results[$key]);
        }


        $results = CategoryHomeResource::collection($results);
        return response()->json([
            'message'       => 'success',
            'status_code'   => 200,
            'data'          =>$results,
        ]);
        
    }
}
