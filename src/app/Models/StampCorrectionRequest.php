<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'target_date',
        'status',
        'requested_clock_in',
        'requested_clock_out',
        'requested_break1_in',
        'requested_break1_out',
        'requested_break2_in',
        'requested_break2_out',
        'reason',
        'note',
    ];

    public function user()
    {
    return $this->belongsTo(\App\Models\User::class);
    }

    public function attendance()
    {
    return $this->belongsTo(\App\Models\Attendance::class);
    }

}
