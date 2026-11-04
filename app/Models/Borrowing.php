<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Borrowing extends Model {
    protected $table = 'borrowings';
    protected $fillable = ['id','user_id','unit_id','locker_id','approved_by','status','start_date','due_date','picked_up_at','returned_at'];
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function unit() { return $this->belongsTo(HardwareUnit::class, 'unit_id'); }
    public function locker() { return $this->belongsTo(Locker::class, 'locker_id'); }
    protected $casts = ['id'=>'integer','start_date'=>'date','due_date'=>'date','picked_up_at'=>'datetime','returned_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime'];
}
