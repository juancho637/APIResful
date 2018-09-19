<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Seller;
use App\Transformers\ProductTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('transform.input:'.ProductTransformer::class)->only(['store', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Seller $seller
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;

        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $seller
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, User $seller)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['status'] = Product::PRODUCT_NOT_AVAILABLE;
        if (!empty($request->image)) {
            $data['image'] = $request->image->store('');
        }
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Seller $seller
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in: '.Product::PRODUCT_NOT_AVAILABLE.', '.Product::PRODUCT_AVAILABLE,
            'image' => 'image',
        ];

        $this->validate($request, $rules);

        $this->verifiedSeller($seller, $product);

        $product->fill($request->intersect(['name', 'description', 'quantity']));

        if ($request->has('status')){
            $product->status = $request->status;

            if ($product->itsAvailable() && $product->categories()->count() == 0){
                return $this->errorResponse('An active product must have at least one category', 409);
            }
        }

        if ($request->hasFile('image')){
            Storage::delete($product->image);

            $product->image = $request->image->store('');
        }

        if (!$product->isDirty()){
            return $this->errorResponse('Please enter at least one field to update', 422);
        }

        $product->save();
        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller $seller
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->verifiedSeller($seller, $product);
        Storage::delete($product->image);
        $product->delete();

        return $this->showOne($product);
    }

    /**
     * @param Seller $seller
     * @param Product $product
     */
    protected function verifiedSeller(Seller $seller, Product $product){
        if ($seller->id != $product->seller_id){
            throw new HttpException(422, 'This product does not belong to this seller');
        }
    }
}
