<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPackage extends Model
{
    protected $table = 'tour_packages';

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'satuan',
        'tag',
        'durasi',
        'min_participants',
        'max_participants',
        'gambar',
        'is_active',
        'created_by',
    ];

    public function includes(): HasMany
    {
        return $this->hasMany(TourPackageInclude::class, 'package_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'package_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
