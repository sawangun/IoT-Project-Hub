<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KitItem extends Model {
    protected $table = 'kit_items';
    protected $fillable = ['kit_id','component_id','qty'];
    protected $casts = ['qty'=>'integer'];
    public $timestamps = false;
}
