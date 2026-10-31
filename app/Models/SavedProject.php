<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SavedProject extends Model {
    protected $table = 'saved_projects';
    protected $fillable = ['user_id','project_id'];
    protected $casts = ['saved_at'=>'datetime'];
    public $timestamps = false;
}
