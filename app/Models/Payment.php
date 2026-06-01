<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id','course_id','amount','currency','provider','provider_reference','status','metadata','access_period','coupon_id','discount_amount','subtotal_amount'];
    protected $casts = ['metadata' => 'array', 'discount_amount' => 'decimal:2', 'subtotal_amount' => 'decimal:2'];
    public function user()   { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function refundRequest() { return $this->hasOne(RefundRequest::class); }
}
