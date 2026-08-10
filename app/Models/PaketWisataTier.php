<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketWisataTier extends Model
{
    use SoftDeletes;

    protected $table = 'paket_wisata_tier';

    protected $fillable = [
        'paket_id',
        'min_peserta',
        'harga_per_orang',
    ];

    protected function casts(): array
    {
        return [
            'min_peserta' => 'integer',
            'harga_per_orang' => 'decimal:2',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketWisata::class, 'paket_id');
    }
}
