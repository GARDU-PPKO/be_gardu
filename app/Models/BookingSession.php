<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSession extends Model
{
    protected $table = 'booking_sessions';

    protected $fillable = [
        'paket_wisata_id',
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
        return Booking::where('paket_wisata_id', $this->paket_wisata_id)
            ->where('sesi', $this->sesi)
            ->where('tanggal_kunjungan', $tanggal)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->sum('jumlah_peserta');
    }

    public function sisaPadaTanggal(string $tanggal): int
    {
        return max(0, (int) $this->kuota - $this->terisiPadaTanggal($tanggal));
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketWisata::class, 'paket_wisata_id');
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
