<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudayaSchedule extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'budaya_schedules';

    protected $fillable = [
        'budaya_id',
        'nama_acara',
        'hari',
        'jam',
        'deskripsi',
        'is_active',
    ];

    public function budaya(): BelongsTo
    {
        return $this->belongsTo(Budaya::class, 'budaya_id');
    }
}
