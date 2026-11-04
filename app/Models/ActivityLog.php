<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model {
    protected $table = 'activity_logs';
    protected $fillable = ['id','user_id','locker_id','action'];
    protected $casts = ['id'=>'integer','created_at'=>'datetime'];
    public $timestamps = false;
}
