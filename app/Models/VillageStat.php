<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillageStat extends Model
{
    protected $table = 'village_stats';

    protected $fillable = [
        'label',
        'nilai',
        'satuan',
        'icon',
        'urutan',
        'is_active',
    ];
}
