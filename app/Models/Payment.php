<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id','course_id','amount','currency','provider','provider_reference','status','metadata','access_period','coupon_id','discount_amount','subtotal_amount','gift_token','gift_recipient_email','gift_recipient_name','gift_message','gift_redeemed_at','gift_redeemed_by','subscription_id','bundle_id'];
    protected $casts = ['metadata' => 'array', 'discount_amount' => 'decimal:2', 'subtotal_amount' => 'decimal:2', 'gift_redeemed_at' => 'datetime'];
    public function user()   { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function refundRequest() { return $this->hasOne(RefundRequest::class); }
}
