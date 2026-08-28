<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'session_id',
        'visited_date',
    ];

    protected $casts = [
        'visited_date' => 'date',
    ];
}
