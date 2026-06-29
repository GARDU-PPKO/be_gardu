<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DusunGallery extends Model
{
    protected $table = 'dusun_galleries';

    protected $fillable = [
        'dusun_id',
        'image_url',
        'urutan',
    ];

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class, 'dusun_id');
    }
}
