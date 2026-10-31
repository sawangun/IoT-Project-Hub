<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QrToken extends Model {
    protected $table = 'qr_tokens';
    protected $fillable = ['id','borrowing_id','token','expires_at','status'];
    protected $casts = ['id'=>'integer','expires_at'=>'datetime','created_at'=>'datetime'];
    public $timestamps = false;
}
