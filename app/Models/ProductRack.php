<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRack extends Model
{
    use HasFactory;

    protected $table = 'product_racks';
    protected $guarded = ['id'];
}
