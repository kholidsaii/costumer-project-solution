<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = ['order_id', 'invoice_number', 'amount', 'status', 'due_date', 'paid_at'];

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
