<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id', 'room_booking_id', 'start_date', 'end_date', 'reason', 'status', 'reviewed_at'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roomBooking()
    {
        return $this->belongsTo(RoomBooking::class);
    }
}
