<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class RoleMiddleware {
 public function handle(Request $request,Closure $next,string ...$roles): Response {
  if(!$request->user()) return redirect()->route('login');
  $role=$request->user()->role;
  $allowed=in_array($role,$roles,true) || (in_array('pengguna',$roles,true) && in_array($role,['mahasiswa','umum'],true));
  if(!$allowed) abort(403,'Anda tidak memiliki hak akses ke halaman ini.');
  return $next($request);
 }
}
