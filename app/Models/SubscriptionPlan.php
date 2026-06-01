<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price_usd', 'price_zwg', 'interval', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'price_usd' => 'decimal:2', 'price_zwg' => 'decimal:2'];
}
