<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStat extends Model
{
    protected $table = 'booking_stats_monthly';

    protected $fillable = [
        'year_month',
        'total_booking',
        'total_confirmed',
        'total_revenue',
        'total_pengunjung',
    ];

    protected function casts(): array
    {
        return [
            'total_booking' => 'integer',
            'total_confirmed' => 'integer',
            'total_revenue' => 'decimal:2',
            'total_pengunjung' => 'integer',
        ];
    }
}
