<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $table = 'orders';
    protected $fillable = ['id','user_id','total','payment_method','status'];
    public function items() { return $this->hasMany(OrderItem::class, 'order_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    protected $casts = ['id'=>'integer','total'=>'decimal:2','created_at'=>'datetime','updated_at'=>'datetime'];
}
