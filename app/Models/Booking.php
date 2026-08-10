<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    public const STATUS_PENDING_VERIFY = 'PENDING_VERIFY';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'bookings';

    protected $fillable = [
        'booking_code',
        'nama_lengkap',
        'no_whatsapp',
        'email',
        'alamat',
        'kontak_darurat_nama',
        'kontak_darurat_telp',
        'notes',
        'jumlah_peserta',
        'tanggal_kunjungan',
        'paket_wisata_id',
        'sesi',
        'total_harga',
        'bukti_pembayaran_path',
        'nominal_transfer',
        'metode_pembayaran',
        'status',
        'rejected_reason',
        'verified_by',
        'verified_at',
        'raw_wa_text',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kunjungan' => 'date',
            'total_harga' => 'decimal:2',
            'nominal_transfer' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public static function generateBookingCode(): string
    {
        do {
            $code = 'GRD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (static::withTrashed()->where('booking_code', $code)->exists());

        return $code;
    }

    public function paketWisata(): BelongsTo
    {
        return $this->belongsTo(PaketWisata::class, 'paket_wisata_id');
    }

    public function package(): BelongsTo
    {
        return $this->paketWisata();
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BookingLog::class, 'booking_id')->latest('created_at');
    }

    public function isFinalStatus(): bool
    {
        return in_array($this->status, [
            self::STATUS_REJECTED,
            self::STATUS_EXPIRED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ], true);
    }
}
