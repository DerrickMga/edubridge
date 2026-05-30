<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WhatsappWebhook extends Model
{
    protected $fillable = ['from_phone','message_id','type','content','payload','processed'];
    protected $casts = ['payload' => 'array', 'processed' => 'boolean'];
}
