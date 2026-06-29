<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillageProfile extends Model
{
    protected $table = 'village_profile';

    protected $fillable = [
        'tipe',
        'judul',
        'konten',
        'urutan',
        'is_active',
        'created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
