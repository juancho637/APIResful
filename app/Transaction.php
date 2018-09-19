<?php

namespace App;

use App\Transformers\TransactionTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed product
 * @property mixed id
 * @property mixed quantity
 * @property mixed buyer_id
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed product_id
 */
class Transaction extends Model
{
    use SoftDeletes;

    public $transformer = TransactionTransformer::class;
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'buyer_id',
        'product_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    /*public function seller(){
        return $this->belongsTo(Seller::class);
    }*/

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer(){
        return $this->belongsTo(Buyer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
