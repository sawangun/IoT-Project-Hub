<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Kit extends Model {
    protected $table = 'kits';
    protected $fillable = ['id','project_id','name','price','stock','image_url'];
    public function components() { return $this->belongsToMany(Component::class, 'kit_items', 'kit_id', 'component_id')->withPivot('qty'); }
    public function project() { return $this->belongsTo(Project::class, 'project_id'); }
    protected $casts = ['id'=>'integer','price'=>'decimal:2','stock'=>'integer','created_at'=>'datetime','updated_at'=>'datetime'];
}
