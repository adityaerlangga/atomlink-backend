<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackCategory extends Model
{
    use HasFactory;

    protected $table = 'rack_categories';
    protected $guarded = ['id'];
}
