<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $role = (string) $user->role;
        // Admin boleh membuka halaman area pengguna juga. Sebaliknya, role pengguna
        // tidak memperoleh akses ke halaman admin karena grup admin tetap role:admin.
        $allowed = in_array($role, $roles, true)
            || (in_array('pengguna', $roles, true) && in_array($role, ['mahasiswa', 'umum'], true))
            || ($role === 'admin' && in_array('pengguna', $roles, true));

        if (! $allowed) {
            // Send authenticated users to the area appropriate for their role
            // instead of showing an authorization error or exposing another role's pages.
            if ($role === 'admin' && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }

            if (in_array($role, ['mahasiswa', 'umum', 'pengguna'], true) && Route::has('dashboard')) {
                return redirect()->route('dashboard');
            }

            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
