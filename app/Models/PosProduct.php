<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProduct extends Model
{
    use HasFactory;

    protected $table = 'pos_products';

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'price',
        'stock',
        'is_active',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(PosCategory::class, 'category_id');
    }
}
