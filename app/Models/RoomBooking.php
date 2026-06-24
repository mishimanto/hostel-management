<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'room_id',
        'monthly_rent',
        'requested_start_date',
        'requested_end_date',
        'total_days',
        'payable_amount',
        'payment_method',
        'transaction_id',
        'payment_details',
        'paid_until',
        'started_at',
        'note',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'requested_start_date' => 'date',
            'requested_end_date' => 'date',
            'payable_amount' => 'decimal:2',
            'paid_until' => 'date',
            'started_at' => 'date',
            'reviewed_at' => 'datetime',
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
}
