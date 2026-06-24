<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'room_booking_id',
        'room_id',
        'invoice_no',
        'billing_month',
        'due_date',
        'amount_due',
        'amount_paid',
        'adjustment_amount',
        'status',
        'transaction_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_month' => 'date',
            'due_date' => 'date',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Payment $payment): void {
            $netDue = max(0, (float) $payment->amount_due - (float) $payment->adjustment_amount);
            $paid = (float) $payment->amount_paid;
            $payment->status = $paid <= 0 ? 'due' : ($paid >= $netDue ? 'paid' : 'partial');
            $payment->paid_at = $payment->status === 'paid' ? ($payment->paid_at ?? now()) : null;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roomBooking()
    {
        return $this->belongsTo(RoomBooking::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
