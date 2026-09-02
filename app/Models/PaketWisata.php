<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketWisata extends Model
{
    use SoftDeletes;

    protected $table = 'paket_wisata';

    protected $fillable = [
        'nama',
        'kategori',
        'tipe_harga',
        'kapasitas_per_unit',
        'harga_paket',
        'deskripsi',
        'fasilitas',
        'gambar',
        'tag',
        'durasi',
        'aktif',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'fasilitas' => 'array',
            'harga_paket' => 'decimal:2',
            'kapasitas_per_unit' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (PaketWisata $paket) {
            if (! $paket->isForceDeleting()) {
                $paket->tiers()->delete();
            }
        });
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(PaketWisataTier::class, 'paket_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'paket_wisata_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PackageReview::class, 'paket_wisata_id');
    }

    public function visibleReviews(): HasMany
    {
        return $this->hasMany(PackageReview::class, 'paket_wisata_id')->where('is_visible', true)->latest();
    }

    public function getRatingAvgAttribute(): ?float
    {
        $avg = $this->reviews()->where('is_visible', true)->avg('rating');
        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->where('is_visible', true)->count();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hitung total harga sesuai tipe harga paket.
     *
     * @return array{harga_per_orang: float|null, jumlah_unit: int|null, total: float}
     */
    public function hitungTotalHarga(int $jumlahPeserta): array
    {
        if ($this->tipe_harga === 'per_orang_tier') {
            $tier = $this->tiers()
                ->where('min_peserta', '<=', $jumlahPeserta)
                ->orderByDesc('min_peserta')
                ->first();

            if (! $tier) {
                throw new \Exception('Jumlah peserta di bawah minimum pemesanan paket ini.');
            }

            return [
                'harga_per_orang' => (float) $tier->harga_per_orang,
                'jumlah_unit' => null,
                'total' => (float) $tier->harga_per_orang * $jumlahPeserta,
            ];
        }

        $jumlahUnit = (int) ceil($jumlahPeserta / max(1, (int) $this->kapasitas_per_unit));

        return [
            'harga_per_orang' => null,
            'jumlah_unit' => $jumlahUnit,
            'total' => (float) $jumlahUnit * (float) $this->harga_paket,
        ];
    }
}
