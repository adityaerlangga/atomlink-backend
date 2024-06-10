<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDeposit extends Model
{
    use HasFactory;

    protected $table = 'service_deposits';
    protected $guarded = ['id'];
}
