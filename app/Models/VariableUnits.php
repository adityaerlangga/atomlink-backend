<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableUnits extends Model
{
    use HasFactory;

    protected $table = 'variable_units';
    protected $guarded = ['id'];
}
