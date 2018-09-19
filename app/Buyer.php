<?php

namespace App;

use App\Scopes\BuyerScope;
use App\Transformers\BuyerTransformer;

/**
 * @property mixed transactions
 */
class Buyer extends User
{
    public $transformer = BuyerTransformer::class;

    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new BuyerScope);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
