<?php

namespace Mrpath\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Mrpath\Category\Repositories\CategoryRepository;
use Mrpath\API\Http\Resources\Catalog\Category as CategoryResource;

class CategoryController extends Controller
{
    /**
     * CategoryRepository object
     *
     * @var \Mrpath\Category\Repositories\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param  Mrpath\Category\Repositories\CategoryRepository  $categoryRepository
     * @return void
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->middleware('validateAPIHeader');

        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CategoryResource::collection(
            $this->categoryRepository->getVisibleCategoryTree(request()->input('parent_id'))
        );
    }
}
