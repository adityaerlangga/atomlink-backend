<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopProductionStep extends Model
{
    use HasFactory;
    protected $table = 'workshop_production_steps';
    protected $guarded = ['id'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class, 'workshop_code', 'workshop_code');
    }
}
