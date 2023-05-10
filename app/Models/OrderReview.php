<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReview extends Model
{
    use HasFactory;
    protected $table = 'orders_reviews';
    protected $guarded = [];


    public function cart()
    {
        return $this->belongsTo(Cart::class, 'order_id');
    }
}
