<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSession extends Model
{
    protected $table = 'booking_sessions';

    protected $fillable = [
        'nama',
        'jam_mulai',
        'jam_selesai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai' => 'string',
            'jam_selesai' => 'string',
        ];
    }
}
