<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = ['id','user_id','type','message','is_read'];
    protected $casts = ['id'=>'integer','is_read'=>'boolean','created_at'=>'datetime'];
}
