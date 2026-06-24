<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'room_booking_id',
        'current_room_id',
        'requested_room_id',
        'change_date',
        'old_monthly_rent',
        'new_monthly_rent',
        'remaining_paid_days',
        'additional_payable',
        'extra_days',
        'new_paid_until',
        'reason',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'change_date' => 'date',
            'old_monthly_rent' => 'decimal:2',
            'new_monthly_rent' => 'decimal:2',
            'additional_payable' => 'decimal:2',
            'new_paid_until' => 'date',
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

    public function currentRoom()
    {
        return $this->belongsTo(Room::class, 'current_room_id');
    }

    public function requestedRoom()
    {
        return $this->belongsTo(Room::class, 'requested_room_id');
    }
}
