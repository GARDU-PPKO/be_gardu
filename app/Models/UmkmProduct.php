<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmkmProduct extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'umkm_products';

    protected $fillable = [
        'nama',
        'kategori',
        'harga',
        'stock',
        'sku',
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
            'stock' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setNoWaPenjualAttribute($value): void
    {
        $digits = preg_replace('/[^\d]/', '', (string) $value);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        $this->attributes['no_wa_penjual'] = $digits;
    }
}
