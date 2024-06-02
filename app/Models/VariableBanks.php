<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableBanks extends Model
{
    use HasFactory;

    protected $table = 'variable_banks';
    protected $guarded = ['id'];
}
