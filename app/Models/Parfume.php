<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parfume extends Model
{
    use HasFactory;
    protected $table = 'parfumes';
    protected $guarded = ['id'];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_code', 'outlet_code');
    }
}
