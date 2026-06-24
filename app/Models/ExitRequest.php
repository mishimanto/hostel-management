<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requested_exit_date',
        'notice_days',
        'rent_due',
        'deposit_adjustment',
        'balance_adjustment',
        'final_payable',
        'final_refundable',
        'reason',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_exit_date' => 'date',
            'rent_due' => 'decimal:2',
            'deposit_adjustment' => 'decimal:2',
            'balance_adjustment' => 'decimal:2',
            'final_payable' => 'decimal:2',
            'final_refundable' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
