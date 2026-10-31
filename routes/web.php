<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectManagementController;
use App\Http\Controllers\Admin\BorrowingManagementController;
use App\Http\Controllers\Admin\InventoryManagementController;
use App\Http\Controllers\UserPortalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\PurchaseOrderController;

// Halaman utama
// Route::get('/', function () {
//     return view('guest.home', [
//         'lockerStats' => [],
//     ]);
// })->name('home');

Route::get('/', function () {
    $projects = \Illuminate\Support\Facades\Schema::hasTable('projects')
        ? \Illuminate\Support\Facades\DB::table('projects')->where('status', 'published')->orderByDesc('created_at')->limit(6)->get()
        : collect();
    return view('guest.home', [
        'projects' => $projects,
        'lockerStats' => [],
    ]);
})->name('home');

// Autentikasi: guest = belum login, pengguna = akun biasa, admin = pengelola.
Route::middleware("guest")->group(function () {
    Route::get("/login", [\App\Http\Controllers\AuthController::class, "showLogin"])->name("login");
    Route::post("/login", [\App\Http\Controllers\AuthController::class, "login"])->name("login.store");
    Route::get("/register", [\App\Http\Controllers\AuthController::class, "showRegister"])->name("register");
    Route::post("/register", [\App\Http\Controllers\AuthController::class, "register"])->name("register.store");
});
Route::post("/logout", [\App\Http\Controllers\AuthController::class, "logout"])->middleware("auth")->name("logout");

// Area admin hanya dapat diakses akun dengan role admin.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/purchase-orders', [PurchaseOrderController::class, 'index'])->name('admin.purchase-orders.index');
    Route::patch('/admin/purchase-orders/{order}/status', [PurchaseOrderController::class, 'updateStatus'])->whereNumber('order')->name('admin.purchase-orders.status');

    // Manajemen proyek admin: data demo disimpan di session, belum memakai database.
    Route::prefix('/admin/projects')->name('admin.projects.')->controller(ProjectManagementController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{project}/edit', 'edit')->whereNumber('project')->name('edit');
        Route::put('/{project}', 'update')->whereNumber('project')->name('update');
        Route::delete('/{project}', 'destroy')->whereNumber('project')->name('destroy');
    });

    // Manajemen peminjaman admin: data demo disimpan di session, belum memakai database.
    Route::prefix('/admin/borrowings')->name('admin.borrowings.')->controller(BorrowingManagementController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::patch('/{borrowing}/status', 'updateStatus')->whereNumber('borrowing')->name('status');
    });

    // Smart locker, kit, dan komponen: data demo disimpan melalui session, tanpa database.
    Route::controller(InventoryManagementController::class)->group(function () {
        Route::get('/admin/lockers', 'lockersIndex')->name('admin.inventory.lockers');
        Route::patch('/admin/lockers/{locker}/status', 'lockerStatus')->whereNumber('locker')->name('admin.inventory.locker-status');
        Route::get('/admin/kits-components', 'kitsIndex')->name('admin.inventory.kits');
        Route::get('/admin/kits-components/kits/create', 'createKit')->name('admin.inventory.kit-create');
        Route::post('/admin/kits-components/kits', 'storeKit')->name('admin.inventory.kit-store');
        Route::get('/admin/kits-components/components/create', 'createComponent')->name('admin.inventory.component-create');
        Route::post('/admin/kits-components/components', 'storeComponent')->name('admin.inventory.component-store');
    });


    // Halaman verifikasi & otorisasi sesuai desain admin terbaru.
    Route::controller(ApprovalController::class)->group(function () {
        Route::get('/admin/project-approval', 'projects')->name('admin.approvals.projects');
        Route::get('/admin/borrowing-approval', 'borrowings')->name('admin.approvals.borrowings');
        Route::post('/admin/borrowing-approval/{borrowing}/approve', 'approveBorrowing')->whereNumber('borrowing')->name('admin.approvals.borrowings.approve');
        Route::post('/admin/borrowing-approval/{borrowing}/reject', 'rejectBorrowing')->whereNumber('borrowing')->name('admin.approvals.borrowings.reject');
        Route::get('/admin/qr-codes/{token?}', 'qrDetail')->name('admin.qr.show');
    });

    // Laporan, audit trail, dan pengaturan sistem.
    Route::controller(SystemController::class)->group(function () {
        Route::get('/admin/reports', 'reports')->name('admin.reports');
        Route::get('/admin/activity-log', 'activity')->name('admin.activity');
        Route::get('/admin/settings', 'settings')->name('admin.settings');
    });

    // Manajemen pengguna, profil admin, dan pusat bantuan.
    Route::controller(UserManagementController::class)->group(function () {
        Route::get('/admin/users', 'index')->name('admin.users.index');
        Route::get('/admin/users/create', 'create')->name('admin.users.create');
        Route::post('/admin/users', 'store')->name('admin.users.store');
        Route::get('/admin/users/{user}', 'show')->whereNumber('user')->name('admin.users.show');
        Route::get('/admin/profile', 'profile')->name('admin.profile');
        Route::get('/admin/help-center', 'help')->name('admin.help');
    });

});

// Alias lama agar tautan /dashboard tetap menuju dashboard admin.
// Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

// Area pengguna hanya dapat diakses akun dengan role pengguna.
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/dashboard', [UserPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil-saya', [UserPortalController::class, 'profile'])->name('user.profile');
    Route::put('/profil-saya', [UserPortalController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/proyek-tersimpan', [ProjectController::class, 'saved'])->name('projects.saved');
    Route::post('/projects/{project}/like', [ProjectController::class, 'toggleSaved'])->whereNumber('project')->name('projects.like');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
    Route::get('/kits-komponen', [UserPortalController::class, 'catalog'])->name('catalog.index');
    Route::get('/peminjaman', [UserPortalController::class, 'borrowings'])->name('borrow.index');
    Route::get('/peminjaman/ajukan/{project}', [UserPortalController::class, 'createBorrowing'])->whereNumber('project')->name('borrow.create');
    Route::post('/peminjaman/ajukan/{project}', [UserPortalController::class, 'submitBorrowing'])->whereNumber('project')->name('borrow.submit');
    Route::get('/peminjaman/{borrowing}/tiket', [UserPortalController::class, 'borrowingTicket'])->whereNumber('borrowing')->name('borrow.ticket');
    Route::post('/peminjaman', [UserPortalController::class, 'storeBorrowing'])->name('borrow.store');

    // Halaman tambahan untuk role pengguna (tanpa role/vendor).
    Route::get('/riwayat-transaksi', [UserPortalController::class, 'history'])->name('user.history');
    Route::get('/checkout', [UserPortalController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout', [UserPortalController::class, 'storePurchase'])->name('checkout.store');
    Route::get('/checkout/{order}/simulasi-pembayaran', [UserPortalController::class, 'paymentSimulation'])->whereNumber('order')->name('checkout.simulation');
    Route::post('/checkout/{order}/simulasi-pembayaran/berhasil', [UserPortalController::class, 'simulatePaymentSuccess'])->whereNumber('order')->name('checkout.simulation.success');
    Route::get('/tentang-kami', [UserPortalController::class, 'about'])->name('about');


});

// use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Route::get('/', function () {
//     return view('home');
// })->name('home');

// Route::middleware('guest')->group(function () {
//     Route::get('/login', [
//         AuthenticatedSessionController::class,
//         'create',
//     ])->name('login');

//     Route::post('/login', [
//         AuthenticatedSessionController::class,
//         'store',
//     ])->name('login.store');

//     Route::get('/register', [
//         AuthenticatedSessionController::class,
//         'register',
//     ])->name('register');

//     Route::post('/register', [
//         AuthenticatedSessionController::class,
//         'storeRegistration',
//     ])->name('register.store');
// });

// Route::middleware('auth')->group(function () {
//     Route::view('/dashboard', 'dashboard.index')
//         ->name('dashboard');

//     Route::post('/logout', [
//         AuthenticatedSessionController::class,
//         'destroy',
//     ])->name('logout');
// });
