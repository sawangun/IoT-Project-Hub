<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HardwareUnit extends Model {
    protected $table = 'hardware_units';
    protected $fillable = ['id','project_id','serial_code','condition','status'];
    protected $casts = ['id'=>'integer'];
    public $timestamps = false;
}
