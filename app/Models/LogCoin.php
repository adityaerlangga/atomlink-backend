<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCoin extends Model
{
    use HasFactory;
    protected $table = 'log_coins';
    protected $guarded = ['id'];
}
