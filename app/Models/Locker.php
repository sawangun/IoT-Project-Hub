<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Locker extends Model {
    protected $table = 'lockers';
    protected $fillable = ['id','code','status','sensor_status'];
    protected $casts = ['id'=>'integer'];
    public $timestamps = false;
}
