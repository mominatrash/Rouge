<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Address extends Model
{
    use HasFactory;
    protected $table = 'addresses';
    protected $guarded = [];

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }
    
}
