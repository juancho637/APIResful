<?php

namespace App;

use App\Transformers\CategoryTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed products
 * @property mixed id
 * @property mixed name
 * @property mixed description
 * @property mixed created_at
 * @property mixed updated_at
 */
class Category extends Model
{
    use SoftDeletes;

    public $transformer = CategoryTransformer::class;
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
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(){
        return $this->belongsToMany(Product::class);
    }
}
