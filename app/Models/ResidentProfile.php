<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResidentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'room_id',
        'seat_id',
        'joined_at',
        'balance',
        'deposit_paid',
        'guardian_name',
        'guardian_phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'balance' => 'decimal:2',
            'deposit_paid' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
