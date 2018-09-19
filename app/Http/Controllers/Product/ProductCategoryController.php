<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Http\Controllers\ApiController;
use App\Product;
use Illuminate\Http\Request;

class ProductCategoryController extends ApiController
{
    /**
     * ProductCategoryController constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['index']);
        $this->middleware('auth:api')->except(['index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Product $product)
    {
        $categories = $product->categories;

        return $this->showAll($categories);
    }

    /**
     * Update the specified resource in storage.
     * TODO: Relación de muchos a muchos -> Tener en cuenta
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Product $product
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product, Category $category)
    {
        // syncWithoutDetaching -> verifica si la categoria ya existe,
        // si ya existe no hace nada, a diferencia de sync que sobre escribe
        $product->categories()->syncWithoutDetaching([$category->id]);

        return $this->showAll($product->categories);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product $product
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product, Category $category)
    {
        if (!$product->categories()->find($category->id)){
            return $this->errorResponse('La categoría especificada no es una categoría de este producto', 404);
        }
        $product->categories()->detach([$category->id]);

        return $this->showAll($product->categories);
    }
}
