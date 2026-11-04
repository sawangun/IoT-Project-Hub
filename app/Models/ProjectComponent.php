<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProjectComponent extends Model {
    protected $table = 'project_components';
    protected $fillable = ['project_id','component_id','qty'];
    protected $casts = ['qty'=>'integer'];
    public $timestamps = false;
}
