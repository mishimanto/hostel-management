<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'phone', 'address', 'rent_due_day'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
