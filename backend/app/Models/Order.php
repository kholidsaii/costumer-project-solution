<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'user_id', 'product_id', 'order_number', 'total_amount', 'status',
    'payment_method', 'payment_proof', 'shipping_address', 'shipping_cost', 'courier', 'software_link'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}