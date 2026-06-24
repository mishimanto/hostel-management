<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'current_seat_id',
        'requested_seat_id',
        'type',
        'current_rent',
        'requested_rent',
        'balance_before',
        'payable_amount',
        'credit_to_next_rent',
        'reason',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_rent' => 'decimal:2',
            'requested_rent' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'payable_amount' => 'decimal:2',
            'credit_to_next_rent' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function currentSeat()
    {
        return $this->belongsTo(Seat::class, 'current_seat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestedSeat()
    {
        return $this->belongsTo(Seat::class, 'requested_seat_id');
    }
}
