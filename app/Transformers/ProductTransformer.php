<?php

namespace App\Transformers;

use App\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Product $product
     * @return array
     */
    public function transform(Product $product)
    {
        return [
            'identificador' => (int)$product->id,
            'titulo' => (string)$product->name,
            'descripcion' => (string)$product->description,
            'cantidad' => (int)$product->quantity,
            'statdo' => (string)$product->status,
            'imagen' => $product->image, //url("{images/...}"),
            'fechaCreacion' => (string)$product->created_at,
            'fechaActualizacion' => (string)$product->updated_at,
            'fechaEleminacion' => isset($product->delete_at) ? (string)$product->delete_at : null,
            'links'=>[
                [
                    'rel'=>'self',
                    'href'=>route('products.show', $product->id),
                ],
                [
                    'rel'=>'product.buyers',
                    'href'=>route('products.buyers.index', $product->id),
                ],
                [
                    'rel'=>'product.categories',
                    'href'=>route('products.categories.index', $product->id),
                ],
                [
                    'rel'=>'product.transactions',
                    'href'=>route('products.transactions.index', $product->id),
                ],
                [
                    'rel'=>'seller',
                    'href'=>route('sellers.show', $product->seller_id),
                ]
            ],
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index){
        $attributes = [
            'identificador' => 'id',
            'titulo' => 'name',
            'descripcion' => 'description',
            'cantidad' => 'quantity',
            'statdo' => 'status',
            'imagen' => 'image',
            'fechaCreacion' => 'created_at',
            'fechaActualizacion' => 'updated_at',
            'fechaEleminacion' => 'deleted_at',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function transformedAttribute($index){
        $attributes = [
            'id' => 'identificador',
            'name' => 'titulo',
            'description' => 'descripcion',
            'quantity' => 'cantidad',
            'status' => 'statdo',
            'image' => 'imagen',
            'created_at' => 'fechaCreacion',
            'updated_at' => 'fechaActualizacion',
            'deleted_at' => 'fechaEleminacion',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
