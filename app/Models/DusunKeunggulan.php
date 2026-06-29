<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DusunKeunggulan extends Model
{
    protected $table = 'dusun_keunggulan';

    protected $fillable = [
        'dusun_id',
        'keunggulan',
        'urutan',
    ];

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class, 'dusun_id');
    }
}
