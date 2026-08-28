<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddOn extends Model
{
    use SoftDeletes;

    protected $table = 'add_ons';

    protected $fillable = [
        'nama',
        'kategori',
        'tipe_harga',
        'harga',
        'deskripsi',
        'gambar',
        'aktif',
        'urutan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_add_on')
            ->withPivot('qty', 'harga_satuan', 'subtotal')
            ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labelSatuan(): string
    {
        return $this->tipe_harga === 'per_orang' ? 'per orang' : 'per unit';
    }

    public function isFree(): bool
    {
        return (float) $this->harga <= 0;
    }
}
