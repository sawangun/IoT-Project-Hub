<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NotificationPreference extends Model {
    protected $table = 'notification_preferences';
    protected $fillable = ['user_id','channel','enabled'];
    protected $casts = ['enabled'=>'boolean'];
    public $timestamps = false;
}
