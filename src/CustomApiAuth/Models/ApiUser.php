<?php

namespace CustomApiAuth\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use CustomApiAuth\Exceptions\MethodNotAvailableException;

class ApiUser extends Model implements
    AuthenticatableContract
{
    use Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function save(array $options = [])
    {
        throw new MethodNotAvailableException("Method save() not available in Model.");
    }
}
