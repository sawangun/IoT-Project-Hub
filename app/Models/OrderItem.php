<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
    protected $table = 'order_items';
    protected $fillable = ['id','order_id','kit_id','component_id','qty','unit_price'];
    protected $casts = ['id'=>'integer','qty'=>'integer','unit_price'=>'decimal:2'];
    public $timestamps = false;
}
