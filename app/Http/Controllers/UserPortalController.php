<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserPortalController extends Controller
{
    private function profileId(): ?string
    {
        $email = auth()->user()?->email;
        if (! $email || ! Schema::hasTable('profiles')) return null;
        return DB::table('profiles')->where('email', $email)->value('id');
    }

    public function dashboard(): View
    {
        $count = fn (string $table, ?callable $query = null) => Schema::hasTable($table)
            ? (($builder = DB::table($table)) && $query ? $query($builder) : DB::table($table)->count())
            : 0;

        $publishedProjects = Schema::hasTable('projects')
            ? DB::table('projects')->where('status', 'published')->orderByDesc('created_at')->limit(4)->get()
            : collect();
        $availableUnits = Schema::hasTable('hardware_units') ? DB::table('hardware_units')->where('status', 'available')->count() : 0;
        $availableLockers = Schema::hasTable('lockers') ? DB::table('lockers')->where('status', 'available')->count() : 0;
        $profileId = $this->profileId();
        $myBorrowings = collect();
        $myBorrowingCount = 0;
        $activeBorrowingCount = 0;
        $unreadNotifications = 0;

        if ($profileId && Schema::hasTable('borrowings')) {
            $myBorrowingCount = DB::table('borrowings')->where('user_id', $profileId)->count();
            $activeBorrowingCount = DB::table('borrowings')->where('user_id', $profileId)->whereIn('status', ['approved', 'active', 'overdue'])->count();
            $myBorrowings = DB::table('borrowings as b')
                ->leftJoin('hardware_units as hu', 'hu.id', '=', 'b.unit_id')
                ->leftJoin('projects as p', 'p.id', '=', 'hu.project_id')
                ->leftJoin('lockers as l', 'l.id', '=', 'b.locker_id')
                ->where('b.user_id', $profileId)
                ->select('b.id', 'b.status', 'b.due_date', 'b.created_at', 'p.title as project_title', 'hu.serial_code', 'l.code as locker_code')
                ->orderByDesc('b.created_at')->limit(3)->get();
        }
        if ($profileId && Schema::hasTable('notifications')) {
            $unreadNotifications = DB::table('notifications')->where('user_id', $profileId)->where('is_read', false)->count();
        }

        return view('dashboard.index', [
            'publishedProjects' => $publishedProjects,
            'projectCount' => $count('projects', fn ($q) => $q->where('status', 'published')->count()),
            'componentCount' => $count('components'),
            'kitCount' => $count('kits'),
            'availableUnits' => $availableUnits,
            'availableLockers' => $availableLockers,
            'myBorrowingCount' => $myBorrowingCount,
            'activeBorrowingCount' => $activeBorrowingCount,
            'myBorrowings' => $myBorrowings,
            'unreadNotifications' => $unreadNotifications,
            'profileReady' => (bool) $profileId,
        ]);
    }

    public function catalog(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'semua');
        $kits = collect();
        $components = collect();

        if (Schema::hasTable('kits') && Schema::hasTable('projects')) {
            $kitsQuery = DB::table('kits as k')->join('projects as p', 'p.id', '=', 'k.project_id')
                ->where('p.status', 'published')
                ->select('k.id', 'k.name', 'k.price', 'k.stock', 'p.id as project_id', 'p.title as project_title', 'p.difficulty', 'p.description as project_description');
            if ($q !== '') $kitsQuery->where(function ($query) use ($q) { $query->where('k.name', 'ilike', "%{$q}%")->orWhere('p.title', 'ilike', "%{$q}%"); });
            $kits = $kitsQuery->orderBy('k.name')->get();
        }
        if (Schema::hasTable('components')) {
            $componentsQuery = DB::table('components')->select('id', 'name', 'spec', 'price', 'stock', 'low_stock_limit', 'shopee_url');
            if ($q !== '') $componentsQuery->where(function ($query) use ($q) { $query->where('name', 'ilike', "%{$q}%")->orWhere('spec', 'ilike', "%{$q}%"); });
            $components = $componentsQuery->orderBy('name')->get();
        }

        return view('catalog.index', compact('kits', 'components', 'q', 'type'));
    }

    public function borrowings(): View
    {
        $profileId = $this->profileId();
        $borrowings = collect();
        $availableUnits = collect();
        $lockerStats = ['total' => 0, 'available' => 0, 'occupied' => 0, 'maintenance' => 0, 'offline' => 0];

        if (Schema::hasTable('lockers')) {
            foreach (DB::table('lockers')->select('status', DB::raw('count(*) as aggregate'))->groupBy('status')->get() as $row) {
                $lockerStats['total'] += (int) $row->aggregate;
                if (array_key_exists($row->status, $lockerStats)) $lockerStats[$row->status] = (int) $row->aggregate;
            }
        }
        if ($profileId && Schema::hasTable('borrowings')) {
            $borrowings = DB::table('borrowings as b')
                ->leftJoin('hardware_units as hu', 'hu.id', '=', 'b.unit_id')
                ->leftJoin('projects as p', 'p.id', '=', 'hu.project_id')
                ->leftJoin('lockers as l', 'l.id', '=', 'b.locker_id')
                ->where('b.user_id', $profileId)
                ->whereIn('b.status', ['pending', 'approved', 'active', 'overdue', 'rejected'])
                ->select('b.*', 'hu.serial_code', 'p.title as project_title', 'l.code as locker_code')
                ->orderByDesc('b.created_at')->get();
        }
        $selectedProjectId = request()->integer('project_id') ?: null;
        if (Schema::hasTable('hardware_units') && Schema::hasTable('projects')) {
            $availableQuery = DB::table('hardware_units as hu')->join('projects as p', 'p.id', '=', 'hu.project_id')
                ->where('hu.status', 'available')->where('p.status', 'published')
                ->select('hu.id', 'hu.serial_code', 'hu.condition', 'p.id as project_id', 'p.title', 'p.difficulty');
            if ($selectedProjectId) $availableQuery->where('p.id', $selectedProjectId);
            $availableUnits = $availableQuery->orderBy('p.title')->get();
        }

        return view('borrow.index', [
            'borrowings' => $borrowings,
            'availableUnits' => $availableUnits,
            'lockerStats' => $lockerStats,
            'profileId' => $profileId,
            'showAvailableUnits' => request()->query('tab') === 'tersedia',
            'selectedProjectId' => $selectedProjectId,
        ]);
    }


    public function createBorrowing(int $project): View
    {
        $profileId = $this->profileId();
        abort_unless($profileId, 403, 'Profil akun kampus belum terhubung. Lengkapi profil sebelum mengajukan peminjaman.');
        abort_unless(Schema::hasTable('projects') && Schema::hasTable('hardware_units'), 503, 'Data proyek dan perangkat belum tersedia.');
        $projectData = DB::table('projects')->where('id', $project)->where('status', 'published')->first();
        abort_if(!$projectData, 404);
        $units = DB::table('hardware_units')->where('project_id', $project)->where('status', 'available')->orderBy('serial_code')->get();
        $profile = DB::table('profiles')->where('id', $profileId)->first();
        return view('borrow.application', compact('projectData', 'units', 'profile'));
    }

    public function submitBorrowing(Request $request, int $project): RedirectResponse
    {
        $profileId = $this->profileId();
        abort_unless($profileId, 403, 'Profil akun kampus belum terhubung.');
        abort_unless(Schema::hasTable('borrowings') && Schema::hasTable('hardware_units'), 503, 'Fitur peminjaman belum tersedia.');
        abort_unless(DB::table('projects')->where('id', $project)->where('status', 'published')->exists(), 404);
        $data = $request->validate([
            'unit_id' => ['required', 'integer'],
            'purpose' => ['required', 'string', 'min:10', 'max:1000'],
            'activity_type' => ['required', 'in:tugas_akhir,praktikum,riset,eksplorasi'],
            'due_date' => ['required', 'date', 'after_or_equal:tomorrow', 'before_or_equal:'.now()->addDays(30)->toDateString()],
            'agree' => ['accepted'],
        ]);
        $unit = DB::table('hardware_units')->where('id', $data['unit_id'])->where('project_id', $project)->where('status', 'available')->first();
        abort_unless($unit, 422, 'Unit yang dipilih sudah tidak tersedia. Silakan muat ulang halaman.');
        $borrowingId = DB::transaction(function () use ($profileId, $data, $unit) {
            $values = [
                'user_id' => $profileId, 'unit_id' => $unit->id, 'status' => 'pending',
                'start_date' => now()->toDateString(), 'due_date' => $data['due_date'],
                'created_at' => now(), 'updated_at' => now(),
            ];
            if (Schema::hasColumn('borrowings', 'purpose')) $values['purpose'] = $data['purpose'];
            if (Schema::hasColumn('borrowings', 'activity_type')) $values['activity_type'] = $data['activity_type'];
            return DB::table('borrowings')->insertGetId($values);
        });
        return redirect()->route('borrow.ticket', $borrowingId)->with('submitted', true);
    }

    public function borrowingTicket(int $borrowing): View
    {
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('borrowings'), 403);
        $loan = DB::table('borrowings as b')
            ->join('hardware_units as hu', 'hu.id', '=', 'b.unit_id')
            ->join('projects as p', 'p.id', '=', 'hu.project_id')
            ->leftJoin('lockers as l', 'l.id', '=', 'b.locker_id')
            ->leftJoin('qr_tokens as q', 'q.borrowing_id', '=', 'b.id')
            ->where('b.id', $borrowing)->where('b.user_id', $profileId)
            ->select('b.*', 'hu.serial_code', 'p.title as project_title', 'l.code as locker_code', 'q.token', 'q.expires_at as qr_expires_at', 'q.status as qr_status')->first();
        abort_if(!$loan, 404);
        return view('borrow.ticket', compact('loan'));
    }

    public function storeBorrowing(Request $request): RedirectResponse
    {
        $profileId = $this->profileId();
        abort_unless($profileId, 403, 'Silakan masuk dengan akun yang terhubung ke profil kampus terlebih dahulu.');
        abort_unless(Schema::hasTable('hardware_units') && Schema::hasTable('borrowings'), 503, 'Fitur peminjaman belum tersedia.');

        $data = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:hardware_units,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(30)->toDateString()],
        ]);
        $unit = DB::table('hardware_units as hu')->join('projects as p', 'p.id', '=', 'hu.project_id')
            ->where('hu.id', $data['unit_id'])->where('hu.status', 'available')->where('p.status', 'published')
            ->select('hu.id')->first();
        abort_unless($unit, 422, 'Unit tidak tersedia untuk dipinjam.');

        DB::transaction(function () use ($profileId, $data) {
            DB::table('borrowings')->insert([
                'user_id' => $profileId,
                'unit_id' => $data['unit_id'],
                'status' => 'pending',
                'start_date' => now()->toDateString(),
                'due_date' => $data['due_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('borrow.index')->with('success', 'Pengajuan peminjaman berhasil dikirim dan menunggu persetujuan admin.');
    }


    /**
     * Form inspeksi pengembalian perangkat.
     */
    public function returnForm(int $borrowing): View
    {
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('borrowings'), 403);

        $loan = DB::table('borrowings as b')
            ->join('hardware_units as hu', 'hu.id', '=', 'b.unit_id')
            ->join('projects as p', 'p.id', '=', 'hu.project_id')
            ->leftJoin('lockers as l', 'l.id', '=', 'b.locker_id')
            ->where('b.id', $borrowing)
            ->where('b.user_id', $profileId)
            ->select('b.*', 'hu.serial_code', 'hu.condition as unit_condition', 'p.title as project_title', 'l.code as locker_code')
            ->first();

        abort_unless($loan, 404);
        abort_unless(in_array($loan->status, ['active', 'overdue'], true), 403, 'Form pengembalian hanya tersedia untuk peminjaman yang sedang aktif.');

        return view('borrow.return', compact('loan'));
    }

    /**
     * Catat bukti pengembalian. Foto wajib berasal dari kamera browser,
     * sedangkan waktu pada server menjadi timestamp resmi pencatatan.
     */
    public function returnBorrowing(Request $request, int $borrowing): RedirectResponse
    {
        $profileId = $this->profileId();
        abort_unless($profileId, 403, 'Profil akun kampus belum terhubung.');
        abort_unless(Schema::hasTable('borrowings') && Schema::hasTable('hardware_units'), 503, 'Data peminjaman belum tersedia.');

        foreach (['return_photo_path', 'return_photo_taken_at', 'return_condition', 'return_checklist', 'return_notes'] as $column) {
            abort_unless(Schema::hasColumn('borrowings', $column), 503, 'Kolom bukti pengembalian belum tersedia. Jalankan SQL penambahan kolom yang disertakan pada paket aplikasi.');
        }

        $data = $request->validate([
            'return_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:8192'],
            'camera_captured' => ['required', 'in:1'],
            'return_condition' => ['required', 'in:baik,perlu_perbaikan,rusak'],
            'return_checklist' => ['required', 'array', 'min:4'],
            'return_checklist.locker_placed' => ['required', 'accepted'],
            'return_checklist.cables_arranged' => ['required', 'accepted'],
            'return_checklist.components_complete' => ['required', 'accepted'],
            'return_checklist.power_safe' => ['required', 'accepted'],
            'return_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'return_photo.required' => 'Ambil foto bukti pengembalian menggunakan kamera terlebih dahulu.',
            'camera_captured.in' => 'Foto harus diambil langsung dari kamera pada halaman ini.',
            'return_checklist.*.accepted' => 'Pastikan seluruh pemeriksaan pengembalian sudah dicentang.',
        ]);

        $loan = DB::table('borrowings')->where('id', $borrowing)->where('user_id', $profileId)->first();
        abort_unless($loan && in_array($loan->status, ['active', 'overdue'], true), 403, 'Peminjaman ini tidak dapat dikembalikan.');

        $path = $request->file('return_photo')->store("return-evidence/{$borrowing}", 'public');
        if (! $path) {
            return back()->withInput()->with('error', 'Foto gagal disimpan. Silakan ambil ulang foto.');
        }

        try {
            DB::transaction(function () use ($borrowing, $data, $path) {
                $lockedLoan = DB::table('borrowings')->where('id', $borrowing)->lockForUpdate()->first();
                if (! $lockedLoan || ! in_array($lockedLoan->status, ['active', 'overdue'], true)) {
                    throw new \RuntimeException('Status peminjaman sudah berubah. Muat ulang halaman.');
                }

                $now = now();
                DB::table('borrowings')->where('id', $borrowing)->update([
                    'status' => 'returned',
                    'returned_at' => $now,
                    'return_photo_path' => $path,
                    'return_photo_taken_at' => $now,
                    'return_condition' => $data['return_condition'],
                    'return_checklist' => json_encode($data['return_checklist']),
                    'return_notes' => $data['return_notes'] ?? null,
                    'updated_at' => $now,
                ]);

                $unitStatus = $data['return_condition'] === 'baik' ? 'available' : 'maintenance';
                DB::table('hardware_units')->where('id', $lockedLoan->unit_id)->update([
                    'status' => $unitStatus,
                    'condition' => $data['return_condition'],
                ]);

                if ($lockedLoan->locker_id) {
                    DB::table('lockers')->where('id', $lockedLoan->locker_id)->update([
                        'status' => $unitStatus === 'available' ? 'available' : 'maintenance',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return redirect()->route('borrow.index')->with('success', 'Bukti pengembalian berhasil dikirim. Waktu pengembalian sudah tercatat di sistem.');
    }

    /**
     * Riwayat transaksi pembelian dan peminjaman milik pengguna.
     * Pembelian dibaca dari orders dan order_items; peminjaman tetap dari borrowings.
     */
    public function history(): View
    {
        $profileId = $this->profileId();
        $borrowings = collect();
        $purchases = collect();

        if ($profileId && Schema::hasTable('borrowings')) {
            $borrowings = DB::table('borrowings as b')
                ->leftJoin('hardware_units as hu', 'hu.id', '=', 'b.unit_id')
                ->leftJoin('projects as p', 'p.id', '=', 'hu.project_id')
                ->leftJoin('lockers as l', 'l.id', '=', 'b.locker_id')
                ->where('b.user_id', $profileId)
                ->select('b.id', 'b.status', 'b.created_at', 'b.start_date', 'b.due_date',
                    'hu.serial_code', 'p.title as project_title', 'l.code as locker_code')
                ->orderByDesc('b.created_at')->get();
        }

        if ($profileId && Schema::hasTable('orders')) {
            $purchases = DB::table('orders as o')
                ->leftJoin('order_items as oi','oi.order_id','=','o.id')
                ->leftJoin('kits as k','k.id','=','oi.kit_id')
                ->leftJoin('components as c','c.id','=','oi.component_id')
                ->where('o.user_id',$profileId)
                ->select('o.id','o.created_at','o.total','o.payment_method','o.status',
                    DB::raw("string_agg(coalesce(k.name,c.name,'Item'), ', ') as item_name"),
                    DB::raw("CASE WHEN COUNT(oi.kit_id) > 0 THEN 'kit' ELSE 'component' END as item_type"),
                    DB::raw('coalesce(sum(oi.qty),0)::int as quantity'))
                ->groupBy('o.id','o.created_at','o.total','o.payment_method','o.status')
                ->orderByDesc('o.created_at')->get();
        }

        return view('user.history', compact('borrowings', 'purchases', 'profileId'));
    }

    /** Halaman checkout menggunakan item yang dipilih dari katalog. */
    public function checkout(Request $request): View
    {
        $type = (string) $request->query('type', 'component');
        $id = (int) $request->query('id', 0);
        $quantity = max(1, min(20, (int) $request->query('qty', 1)));
        $item = null;

        if ($id > 0 && $type === 'kit' && Schema::hasTable('kits')) {
            $item = DB::table('kits as k')->join('projects as p', 'p.id', '=', 'k.project_id')
                ->where('k.id', $id)->where('p.status', 'published')
                ->select('k.id', 'k.name', 'k.price', 'k.stock')->first();
        } elseif ($id > 0 && $type === 'component' && Schema::hasTable('components')) {
            $item = DB::table('components')->where('id', $id)->select('id', 'name', 'price', 'stock')->first();
        }

        return view('user.checkout', compact('item', 'type', 'id', 'quantity'));
    }

    public function storePurchase(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('orders') && Schema::hasTable('order_items'), 503,
            'Tabel orders dan order_items belum tersedia pada Supabase.');
        $profileId = $this->profileId();
        abort_unless($profileId, 403, 'Profil Supabase tidak ditemukan.');
        abort_unless(DB::table('profiles')->where('id',$profileId)->where('is_verified',true)->exists(),403,'Akun harus terverifikasi sebelum melakukan pemesanan.');

        $data = $request->validate([
            'item_type' => ['required', 'in:kit,component'],
            'item_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:160'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:qris,cod'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $orderId = DB::transaction(function () use ($data, $profileId) {
            $table = $data['item_type'] === 'kit' ? 'kits' : 'components';
            $item = DB::table($table)->where('id',$data['item_id'])->lockForUpdate()->first();
            abort_unless($item,404,'Item katalog tidak ditemukan.');
            abort_if((int)$item->stock < (int)$data['quantity'],422,'Stok item tidak mencukupi.');
            $unitPrice = (float)$item->price;
            DB::table($table)->where('id',$item->id)->decrement('stock',(int)$data['quantity']);

            $id = DB::table('orders')->insertGetId([
                'user_id'=>$profileId,'total'=>$unitPrice*(int)$data['quantity'],
                'payment_method'=>$data['payment_method'],'status'=>'pending',
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('order_items')->insert([
                'order_id'=>$id,
                'kit_id'=>$data['item_type']==='kit' ? $item->id : null,
                'component_id'=>$data['item_type']==='component' ? $item->id : null,
                'qty'=>(int)$data['quantity'],'unit_price'=>$unitPrice,
            ]);
            return $id;
        });

        return redirect()->route('checkout.simulation',$orderId)->with('success',
            'Pesanan berhasil dibuat di Supabase. Lanjutkan simulasi pembayaran.');
    }

    /** Menampilkan ringkasan pembayaran dari orders dan order_items Supabase. */
    public function paymentSimulation(int $order): View
    {
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('orders'),403);
        $purchase = DB::table('orders as o')
            ->leftJoin('order_items as oi','oi.order_id','=','o.id')
            ->leftJoin('kits as k','k.id','=','oi.kit_id')
            ->leftJoin('components as c','c.id','=','oi.component_id')
            ->where('o.id',$order)->where('o.user_id',$profileId)
            ->select('o.*',
                DB::raw("string_agg(coalesce(k.name,c.name,'Item'), ', ') as item_name"),
                DB::raw("CASE WHEN COUNT(oi.kit_id) > 0 THEN 'kit' ELSE 'component' END as item_type"),
                DB::raw('coalesce(sum(oi.qty),0)::int as quantity'))
            ->groupBy('o.id')->first();
        abort_unless($purchase,404);
        return view('user.payment-simulation',compact('purchase'));
    }

    /** Menandai pesanan sebagai lunas dalam mode demo; tidak memproses uang sungguhan. */
    public function simulatePaymentSuccess(Request $request, int $order): RedirectResponse
    {
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('orders'),403);
        $purchase = DB::table('orders')->where('id',$order)->where('user_id',$profileId)->first();
        abort_unless($purchase,404);
        abort_unless($purchase->status === 'pending',422,'Pesanan ini tidak dapat dibayar karena statusnya bukan menunggu pembayaran.');
        DB::table('orders')->where('id',$order)->where('user_id',$profileId)->update([
            'status'=>'paid','updated_at'=>now(),
        ]);

        return redirect()->route('user.history')->with('success',
            'Pembayaran simulasi berhasil! Pesanan ditandai lunas. Tidak ada uang sungguhan yang diproses.');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function profile(): View
    {
        $user = auth()->user();
        $profileId = $this->profileId();
        $savedCount = $profileId && Schema::hasTable('saved_projects')
            ? DB::table('saved_projects')->where('user_id', $profileId)->count() : 0;
        $borrowingCount = $profileId && Schema::hasTable('borrowings')
            ? DB::table('borrowings')->where('user_id', $profileId)->count() : 0;
        $projectCount = Schema::hasTable('projects')
            ? DB::table('projects')->where('creator_id', $profileId)->count() : 0;
        $savedProjects = collect();
        if ($profileId && Schema::hasTable('saved_projects') && Schema::hasTable('projects')) {
            $savedProjects = DB::table('projects as p')->join('saved_projects as sp', 'sp.project_id', '=', 'p.id')
                ->where('sp.user_id', $profileId)->where('p.status', 'published')
                ->select('p.id', 'p.title', 'p.difficulty', 'p.description')->orderByDesc('sp.saved_at')->limit(3)->get();
        }
        return view('user.profile', compact('user', 'profileId', 'savedCount', 'borrowingCount', 'projectCount', 'savedProjects'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('profiles'), 403, 'Profil akun belum terhubung.');
        DB::table('profiles')->where('id', $profileId)->update([
            'name' => $data['name'], 'nim' => $data['nim'] ?? null,
            'phone' => $data['phone'] ?? null, 'updated_at' => now(),
        ]);
        auth()->user()->name = $data['name'];
        auth()->user()->nim = $data['nim'] ?? null;
        auth()->user()->phone = $data['phone'] ?? null;
        return redirect()->route('user.profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
