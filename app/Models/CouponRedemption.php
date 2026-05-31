<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $fillable = ['coupon_id', 'user_id', 'payment_id', 'discount_amount', 'currency'];
    protected $casts = ['discount_amount' => 'decimal:2'];

    public function coupon()  { return $this->belongsTo(Coupon::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
}
