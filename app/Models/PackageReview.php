<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageReview extends Model
{
    protected $table = 'package_reviews';

    protected $fillable = [
        'paket_wisata_id',
        'booking_id',
        'nama_pengulas',
        'rating',
        'komentar',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function paketWisata(): BelongsTo
    {
        return $this->belongsTo(PaketWisata::class, 'paket_wisata_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
