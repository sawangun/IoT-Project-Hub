<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
 use Notifiable;
 protected $table='profiles'; protected $primaryKey='id'; public $incrementing=false; protected $keyType='string'; public $timestamps=false;
 protected $fillable=['id','name','email','role','nim','phone','is_verified'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['is_verified'=>'boolean','created_at'=>'datetime','updated_at'=>'datetime']; }
}
