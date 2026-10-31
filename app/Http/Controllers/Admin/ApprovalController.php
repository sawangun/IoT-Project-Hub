<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function projects() { return view('admin.approvals.projects'); }

    public function borrowings(): View
    {
        $requests = collect();
        if (Schema::hasTable('borrowings')) {
            $requests = DB::table('borrowings as b')
                ->join('profiles as u', 'u.id', '=', 'b.user_id')
                ->join('hardware_units as hu', 'hu.id', '=', 'b.unit_id')
                ->join('projects as p', 'p.id', '=', 'hu.project_id')
                ->leftJoin('qr_tokens as q', 'q.borrowing_id', '=', 'b.id')
                ->select('b.*', 'u.name as borrower_name', 'u.nim', 'u.email', 'hu.serial_code', 'p.title as project_title', 'q.token as qr_token')
                ->orderByRaw("case when b.status = 'pending' then 0 else 1 end")
                ->orderByDesc('b.created_at')->get();
        }
        return view('admin.approvals.borrowings', compact('requests'));
    }

    public function approveBorrowing(int $borrowing): RedirectResponse
    {
        abort_unless(Schema::hasTable('borrowings') && Schema::hasTable('qr_tokens'), 503, 'Tabel peminjaman dan QR belum tersedia.');
        $adminId = DB::table('profiles')->where('email', auth()->user()->email)->value('id');
        DB::transaction(function () use ($borrowing, $adminId) {
            $loan = DB::table('borrowings')->where('id', $borrowing)->lockForUpdate()->first();
            abort_unless($loan && $loan->status === 'pending', 404, 'Pengajuan tidak ditemukan atau sudah diproses.');
            $unit = DB::table('hardware_units')->where('id', $loan->unit_id)->lockForUpdate()->first();
            abort_unless($unit && $unit->status === 'available', 422, 'Unit sudah tidak tersedia. Pengajuan ini tidak dapat disetujui.');
            $locker = Schema::hasTable('lockers') ? DB::table('lockers')->where('status', 'available')->orderBy('code')->lockForUpdate()->first() : null;
            $token = Str::random(64);
            $expires = now()->addHours(24);
            DB::table('borrowings')->where('id', $borrowing)->update([
                'status' => 'approved', 'approved_by' => $adminId,
                'locker_id' => $locker?->id, 'updated_at' => now(),
            ]);
            if (Schema::hasTable('hardware_units')) DB::table('hardware_units')->where('id', $loan->unit_id)->update(['status' => 'borrowed']);
            DB::table('qr_tokens')->insert([
                'borrowing_id' => $borrowing, 'token' => $token,
                'expires_at' => $expires, 'status' => 'active', 'created_at' => now(),
            ]);
            if ($locker && Schema::hasTable('lockers')) DB::table('lockers')->where('id', $locker->id)->update(['status' => 'occupied']);
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->insert(['user_id' => $loan->user_id, 'type' => 'borrowing_status', 'message' => 'Pengajuan peminjaman #' . $borrowing . ' disetujui. Tiket QR sudah tersedia.', 'is_read' => false, 'created_at' => now()]);
            }
        });
        return back()->with('success', 'Pengajuan disetujui. Token QR telah diterbitkan dan dapat dilihat oleh peminjam.');
    }

    public function rejectBorrowing(int $borrowing): RedirectResponse
    {
        $loan = DB::table('borrowings')->where('id', $borrowing)->where('status', 'pending')->first();
        abort_if(!$loan, 404, 'Pengajuan tidak ditemukan atau sudah diproses.');
        DB::table('borrowings')->where('id', $borrowing)->update(['status' => 'rejected', 'updated_at' => now()]);
        if (Schema::hasTable('notifications')) DB::table('notifications')->insert(['user_id' => $loan->user_id, 'type' => 'borrowing_status', 'message' => 'Pengajuan peminjaman #' . $borrowing . ' belum disetujui admin.', 'is_read' => false, 'created_at' => now()]);
        return back()->with('success', 'Pengajuan peminjaman ditolak.');
    }

    public function qrDetail(string $token = 'QR-00128') { return view('admin.qr.show', compact('token')); }
}
