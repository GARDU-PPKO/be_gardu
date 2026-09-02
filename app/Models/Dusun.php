<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dusun extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'dusun';

    protected $fillable = [
        'nama',
        'rw',
        'deskripsi',
        'detail',
        'hero_img',
        'thumbnail',
        'is_active',
        'created_by',
    ];


    public function galleries(): HasMany
    {
        return $this->hasMany(DusunGallery::class, 'dusun_id');
    }

    public function keunggulan(): HasMany
    {
        return $this->hasMany(DusunKeunggulan::class, 'dusun_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
