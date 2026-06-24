<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = ['room_id', 'label', 'monthly_rent', 'security_deposit', 'is_available'];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
