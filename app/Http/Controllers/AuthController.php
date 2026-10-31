<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    private function supabaseUrl(string $path): string
    {
        return rtrim((string) config('services.supabase.url'), '/') . $path;
    }

    private function supabaseHeaders(?string $token = null): array
    {
        $key = (string) config('services.supabase.key');

        return [
            'apikey' => $key,
            'Authorization' => 'Bearer ' . ($token ?: $key),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function adminHeaders(): array
    {
        $key = (string) config('services.supabase.service_role_key');

        return [
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function finishLogin(Request $request, array $auth): ?RedirectResponse
    {
        $token = $auth['access_token'] ?? null;
        $userId = $auth['user']['id'] ?? null;
        if (! $token || ! $userId) {
            return null;
        }

        $profile = Http::withHeaders($this->supabaseHeaders($token))
            ->timeout(15)
            ->get($this->supabaseUrl('/rest/v1/profiles'), [
                'id' => 'eq.' . $userId,
                'select' => '*',
                'limit' => 1,
            ]);

        if (! $profile->successful() || empty($profile->json()[0])) {
            return null;
        }

        $request->session()->put([
            'supabase_access_token' => $token,
            'supabase_refresh_token' => $auth['refresh_token'] ?? null,
            'supabase_user_id' => $userId,
        ]);

        Auth::login(new User($profile->json()[0]));
        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->role === 'admin' ? route('admin.dashboard') : route('dashboard'));
    }

    private function autoConfirmNewUser(string $userId): bool
    {
        $serviceKey = (string) config('services.supabase.service_role_key');
        if (! app()->environment('local') || ! config('services.supabase.auto_confirm') || $serviceKey === '') {
            return false;
        }

        $response = Http::withHeaders($this->adminHeaders())
            ->timeout(15)
            ->put($this->supabaseUrl('/auth/v1/admin/users/' . $userId), [
                'email_confirm' => true,
            ]);

        return $response->successful();
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $request->session()->forget('supabase_auth_error');
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => session('supabase_auth_error', 'Email atau kata sandi tidak sesuai.')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->role === 'admin' ? route('admin.dashboard') : route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'department' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'phone' => ['nullable', 'string', 'max:30'],
            'account_type' => ['required', 'in:mahasiswa,umum'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'interests' => ['nullable', 'array'],
            'terms' => ['accepted'],
        ]);

        if (! config('services.supabase.url') || ! config('services.supabase.key')) {
            return back()->withErrors(['email' => 'SUPABASE_URL dan SUPABASE_ANON_KEY belum diisi di file .env.'])->withInput($request->except(['password', 'password_confirmation']));
        }

        try {
            $response = Http::withHeaders($this->supabaseHeaders())
                ->timeout(20)
                ->post($this->supabaseUrl('/auth/v1/signup'), [
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'data' => [
                        'name' => $data['name'],
                        'role' => $data['account_type'],
                        'nim' => $data['student_id'] ?? null,
                        'phone' => $data['phone'] ?? null,
                    ],
                ]);

            if (! $response->successful()) {
                return back()->withErrors([
                    'email' => $response->json('msg') ?? $response->json('message') ?? 'Registrasi Supabase gagal.',
                ])->withInput($request->except(['password', 'password_confirmation']));
            }

            $auth = $response->json();

            // When Supabase Confirm Email is OFF, signup returns a session.
            // Use that session immediately instead of telling the user to check email.
            $loggedIn = $this->finishLogin($request, $auth);
            if ($loggedIn) {
                return $loggedIn->with('status', 'Registrasi berhasil. Akun langsung aktif dan Anda sudah masuk.');
            }

            // Optional local-only fallback for projects where an old Supabase
            // setting/account still requires confirmation. Never expose this key
            // to the browser; it is read only from the server .env.
            $userId = $auth['user']['id'] ?? null;
            if ($userId && $this->autoConfirmNewUser($userId)) {
                $login = Http::withHeaders($this->supabaseHeaders())
                    ->timeout(20)
                    ->post($this->supabaseUrl('/auth/v1/token?grant_type=password'), [
                        'email' => $data['email'],
                        'password' => $data['password'],
                    ]);

                if ($login->successful()) {
                    $loggedIn = $this->finishLogin($request, $login->json());
                    if ($loggedIn) {
                        return $loggedIn->with('status', 'Registrasi berhasil. Akun lokal dikonfirmasi otomatis dan Anda sudah masuk.');
                    }
                }
            }

            return redirect()->route('login')->with('status', 'Akun berhasil dibuat, tetapi Supabase belum memberikan sesi login. Periksa Confirm Email pada project yang sama dengan SUPABASE_URL. Untuk auto-confirm dari kode pada localhost, isi SUPABASE_SERVICE_ROLE_KEY dan SUPABASE_AUTO_CONFIRM=true di .env.');
        } catch (Throwable $exception) {
            return back()->withErrors(['email' => 'Tidak dapat menghubungi Supabase: ' . $exception->getMessage()])
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $token = $request->session()->get('supabase_access_token');
        if ($token && config('services.supabase.url') && config('services.supabase.key')) {
            try {
                Http::withHeaders($this->supabaseHeaders($token))
                    ->timeout(10)
                    ->post($this->supabaseUrl('/auth/v1/logout'));
            } catch (Throwable) {
                // Always clear the local session even if the remote logout fails.
            }
        }

        Auth::logout();
        $request->session()->forget(['supabase_access_token', 'supabase_refresh_token', 'supabase_user_id', 'supabase_auth_error']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
