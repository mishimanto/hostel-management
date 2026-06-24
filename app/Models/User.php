<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'nid_number',
        'address',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function roomBookings()
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function activeRoomBooking()
    {
        return $this->hasOne(RoomBooking::class)->where('status', 'approved')->latestOfMany();
    }

    public function roomChangeRequests()
    {
        return $this->hasMany(RoomChangeRequest::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
