<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCategory extends Model
{
    use HasFactory;

    protected $table = 'pos_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products()
    {
        return $this->hasMany(PosProduct::class, 'category_id');
    }
}
