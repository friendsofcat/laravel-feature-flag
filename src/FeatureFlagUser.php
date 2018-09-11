<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class FeatureFlagUser extends Model implements Authenticatable
{

    use AuthenticableTrait;
    protected $table = "users";


    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [ ])
    {
        parent::__construct($attributes);
    }
}
