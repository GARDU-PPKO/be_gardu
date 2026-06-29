<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPackageInclude extends Model
{
    protected $table = 'tour_package_includes';

    protected $fillable = [
        'package_id',
        'item',
        'urutan',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'package_id');
    }
}
