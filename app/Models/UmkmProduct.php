<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmkmProduct extends Model
{
    protected $table = 'umkm_products';

    protected $fillable = [
        'nama',
        'kategori',
        'harga',
        'deskripsi',
        'gambar',
        'no_wa_penjual',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
