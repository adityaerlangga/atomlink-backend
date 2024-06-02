<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;
    protected $table = 'outlets';
    protected $guarded = ['id'];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_code', 'owner_code');
    }
}
