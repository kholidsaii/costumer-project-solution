<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingLead extends Model
{
    protected $table = 'marketing_leads';

    protected $fillable = [
        'lead_name', 'company_name', 'email', 'phone', 
        'source', 'status', 'estimated_value', 'notes', 'user_id'
    ];

    public function pic()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}