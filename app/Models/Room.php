<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['branch_id', 'room_number', 'monthly_rent', 'description', 'status'];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(RoomBooking::class)->where('status', 'approved')->latestOfMany();
    }
}
