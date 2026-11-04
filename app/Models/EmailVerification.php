<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmailVerification extends Model {
    protected $table = 'email_verifications';
    protected $fillable = ['id','user_id','code','expires_at','attempts','locked_until'];
    protected $casts = ['id'=>'integer','expires_at'=>'datetime','attempts'=>'integer','locked_until'=>'datetime','created_at'=>'datetime'];
    public $timestamps = false;
}
