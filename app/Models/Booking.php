<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'kode_booking',
        'nama_pemesan',
        'no_wa_pemesan',
        'email',
        'kota_asal',
        'catatan',
        'package_id',
        'tanggal',
        'sesi',
        'jumlah_peserta',
        'total_harga',
        'status',
        'bukti_bayar',
        'raw_wa_text',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_harga' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'package_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
