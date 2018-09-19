<?php

namespace App;

use App\Transformers\UserTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * @property mixed admin
 * @property mixed verified
 * @property mixed id
 * @property mixed email
 * @property mixed name
 * @property string verification_token
 * @property string password
 * @property mixed created_at
 * @property mixed updated_at
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    const USER_VERIFIED = '1';
    const USER_NOT_VERIFIED = '0';

    const USER_ADMINISTRATOR = 'true';
    const USER_GENERAL = 'false';

    public $transformer = UserTransformer::class;

    protected $table = 'users';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verified',
        'verification_token',
        'admin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    public function setNameAttribute($value){
        $this->attributes['name'] = strtolower($value);
    }

    public function getNameAttribute($value){
        return ucwords($value);
    }

    /**
     * @param $value
     */
    public function setEmailAttribute($value){
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * @return bool
     */
    public function isVerified(){
        return $this->verified == User::USER_NOT_VERIFIED;
    }

    /**
     * @return bool
     */
    public function isAdministrator(){
        return $this->admin == User::USER_ADMINISTRATOR;
    }

    /**
     * @return string
     */
    public static function createVerificationToken(){
        return str_random(40);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function findForPassport($identifier) {
        return User::orWhere('email', $identifier)->orWhere('phone', $identifier)->first();
    }
}
