<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\Workshop;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Owner extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'owners';
    protected $guarded = ['id'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function outlets()
    {
        return $this->hasMany(Outlet::class, 'owner_code', 'owner_code');
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class, 'owner_code', 'owner_code');
    }
}
