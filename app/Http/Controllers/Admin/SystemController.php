<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
class SystemController extends Controller {
 public function reports(){ return view('admin.system.reports'); }
 public function activity(){ return view('admin.system.activity'); }
 public function settings(){ return view('admin.system.settings'); }
}
