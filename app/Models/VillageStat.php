<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VillageStat extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
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
