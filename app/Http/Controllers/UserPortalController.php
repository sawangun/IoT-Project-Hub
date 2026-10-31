<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * Riwayat transaksi pembelian dan peminjaman milik pengguna.
     * Pembelian dibaca dari purchase_orders; peminjaman tetap dari borrowings.
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

        if (Schema::hasTable('purchase_orders')) {
            $query = DB::table('purchase_orders');
            if ($profileId) $query->where('user_id', $profileId);
            else $query->whereRaw('1 = 0');
            $purchases = $query->orderByDesc('created_at')->get();
        }

        return view('user.history', compact('borrowings', 'purchases', 'profileId'));
    }

    /**
     * Halaman checkout menggunakan item yang dipilih dari katalog.
     */
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
            $item = DB::table('components')->where('id', $id)
                ->select('id', 'name', 'price', 'stock')->first();
        }

        return view('user.checkout', compact('item', 'type', 'id', 'quantity'));
    }

    public function storePurchase(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('purchase_orders'), 503,
            'Tabel transaksi pembelian belum tersedia. Jalankan migration terlebih dahulu.');

        $data = $request->validate([
            'item_type' => ['required', 'in:kit,component'],
            'item_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:160'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:qris,edupay,virtual_account'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['item_type'] === 'kit' && Schema::hasTable('kits')) {
            $item = DB::table('kits as k')->join('projects as p', 'p.id', '=', 'k.project_id')
                ->where('k.id', $data['item_id'])->where('p.status', 'published')
                ->select('k.id', 'k.name', 'k.price', 'k.stock')->first();
        } elseif ($data['item_type'] === 'component' && Schema::hasTable('components')) {
            $item = DB::table('components')->where('id', $data['item_id'])
                ->select('id', 'name', 'price', 'stock')->first();
        } else {
            $item = null;
        }

        abort_unless($item, 404, 'Item katalog tidak ditemukan.');
        abort_if((int) $item->stock < (int) $data['quantity'], 422, 'Stok item tidak mencukupi.');

        $unitPrice = (float) $item->price;
        $subtotal = $unitPrice * (int) $data['quantity'];
        $orderId = DB::table('purchase_orders')->insertGetId([
            'user_id' => $this->profileId(),
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'item_type' => $data['item_type'],
            'item_id' => $item->id,
            'item_name' => $item->name,
            'quantity' => $data['quantity'],
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'payment_method' => $data['payment_method'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('checkout.simulation', $orderId)->with('success',
            'Pesanan berhasil dibuat. Ini adalah simulasi pembayaran untuk keperluan demo.');
    }

    /** Menampilkan halaman pembayaran simulasi untuk pesanan milik pengguna. */
    public function paymentSimulation(int $order): View
    {
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('purchase_orders'), 403);
        $purchase = DB::table('purchase_orders')->where('id', $order)->where('user_id', $profileId)->first();
        abort_unless($purchase, 404);
        return view('user.payment-simulation', compact('purchase'));
    }

    /** Menandai pesanan sebagai lunas dalam mode demo; tidak memproses uang sungguhan. */
    public function simulatePaymentSuccess(Request $request, int $order): RedirectResponse
    {
        $profileId = $this->profileId();
        abort_unless($profileId && Schema::hasTable('purchase_orders'), 403);

        DB::transaction(function () use ($profileId, $order) {
            $purchase = DB::table('purchase_orders')->where('id', $order)->where('user_id', $profileId)->lockForUpdate()->first();
            abort_unless($purchase, 404);
            if ($purchase->status === 'paid') return;
            abort_unless($purchase->status === 'pending', 422, 'Pesanan ini tidak dapat dibayar karena statusnya bukan menunggu pembayaran.');

            $table = $purchase->item_type === 'kit' ? 'kits' : 'components';
            abort_unless(Schema::hasTable($table), 503, 'Tabel inventaris belum tersedia.');
            $stockItem = DB::table($table)->where('id', $purchase->item_id)->lockForUpdate()->first();
            abort_unless($stockItem && (int) $stockItem->stock >= (int) $purchase->quantity, 422, 'Stok tidak mencukupi untuk menyelesaikan pesanan ini.');
            DB::table($table)->where('id', $purchase->item_id)->decrement('stock', (int) $purchase->quantity);
            DB::table('purchase_orders')->where('id', $order)->update([
                'status' => 'paid', 'paid_at' => now(), 'updated_at' => now(),
            ]);
        });

        return redirect()->route('user.history')->with('success',
            'Pembayaran simulasi berhasil! Pesanan ditandai Lunas. Tidak ada uang sungguhan yang diproses.');
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
