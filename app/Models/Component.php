<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Component extends Model {
    protected $table = 'components';
    protected $fillable = ['id','name','spec','price','stock','low_stock_limit','shopee_url','image_url'];
    protected $casts = ['id'=>'integer','price'=>'decimal:2','stock'=>'integer','low_stock_limit'=>'integer','created_at'=>'datetime','updated_at'=>'datetime'];
}
