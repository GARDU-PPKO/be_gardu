<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budaya extends Model
{
    protected $table = 'budaya';

    protected $fillable = [
        'judul',
        'kategori',
        'deskripsi',
        'gambar',
        'span_grid',
        'is_active',
        'created_by',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(BudayaSchedule::class, 'budaya_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
