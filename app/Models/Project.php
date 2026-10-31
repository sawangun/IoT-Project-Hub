<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Project extends Model {
    protected $table = 'projects';
    protected $fillable = ['id','creator_id','reviewed_by','title','description','difficulty','schematic_url','repo_url','status','image_url'];
    public function components() { return $this->belongsToMany(Component::class, 'project_components', 'project_id', 'component_id')->withPivot('qty'); }
    public function kits() { return $this->hasMany(Kit::class, 'project_id'); }
    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    protected $casts = ['id'=>'integer','created_at'=>'datetime','updated_at'=>'datetime'];
}
