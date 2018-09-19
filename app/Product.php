<?php

namespace App;

use App\Transformers\ProductTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed seller_id
 * @property mixed status
 * @property mixed transactions
 * @property mixed categories
 * @property mixed seller
 * @property mixed quantity
 * @property mixed id
 * @property mixed image
 * @property mixed name
 * @property mixed description
 * @property mixed updated_at
 * @property mixed created_at
 */
class Product extends Model
{
    use SoftDeletes;

    const PRODUCT_AVAILABLE = 'available';
    const PRODUCT_NOT_AVAILABLE = 'not available';

    public $transformer = ProductTransformer::class;
    protected $dates = ['deleted_at'];
    protected $hidden = [
        'pivot'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'quantity',
        'status',
        'image',
        'seller_id',
    ];

    /**
     * @return bool
     */
    public function itsAvailable(){
        return $this->status == Product::PRODUCT_AVAILABLE;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller(){
        return $this->belongsTo(Seller::class);
    }
}
