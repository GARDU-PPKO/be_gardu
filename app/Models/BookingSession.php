<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSession extends Model
{
    protected $table = 'booking_sessions';

    protected $fillable = [
        'sesi',
        'jam_mulai',
        'jam_selesai',
        'kuota',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'kuota' => 'integer',
        ];
    }

    /**
     * Hitung jumlah peserta yang sudah booking pada sesi ini untuk tanggal tertentu.
     * Hanya booking berstatus CONFIRMED yang mengurangi stok hari itu.
     */
    public function terisiPadaTanggal(string $tanggal): int
    {
        return (int) Booking::where('sesi', $this->sesi)
            ->where('tanggal_kunjungan', $tanggal)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->sum('jumlah_peserta');
    }

    /**
     * Sisa kuota pada tanggal tertentu. Mengembalikan null jika unlimited.
     */
    public function sisaPadaTanggal(string $tanggal): ?int
    {
        if ($this->kuota === null) {
            return null; // Unlimited
        }

        return max(0, (int) $this->kuota - $this->terisiPadaTanggal($tanggal));
    }

    public function package(): BelongsTo
    {
        return $this->paket();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
