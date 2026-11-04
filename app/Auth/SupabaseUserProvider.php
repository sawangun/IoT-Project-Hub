<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

class SupabaseUserProvider implements UserProvider
{
    private function url(string $path): string
    {
        return rtrim((string) config('services.supabase.url'), '/') . $path;
    }

    private function headers(?string $token = null): array
    {
        $key = (string) config('services.supabase.key');

        return [
            'apikey' => $key,
            'Authorization' => 'Bearer ' . ($token ?: $key),
            'Accept' => 'application/json',
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

    public function retrieveById($identifier): ?Authenticatable
    {
        $token = session('supabase_access_token');
        if (! $token || ! config('services.supabase.url') || ! config('services.supabase.key')) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers($token))
                ->timeout(15)
                ->get($this->url('/rest/v1/profiles'), [
                    'id' => 'eq.' . $identifier,
                    'select' => '*',
                    'limit' => 1,
                ]);

            return $response->successful() && ! empty($response->json()[0])
                ? new User($response->json()[0])
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Remember tokens are not managed by Supabase Auth.
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return empty($credentials['email']) ? null : new User(['email' => $credentials['email']]);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $baseUrl = config('services.supabase.url');
        $anonKey = config('services.supabase.key');
        if (! $baseUrl || ! $anonKey) {
            session(['supabase_auth_error' => 'Konfigurasi SUPABASE_URL atau SUPABASE_ANON_KEY belum diisi pada file .env.']);
            return false;
        }

        $email = (string) ($credentials['email'] ?? $user->email ?? '');
        $password = (string) ($credentials['password'] ?? '');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(20)
                ->post($this->url('/auth/v1/token?grant_type=password'), [
                    'email' => $email,
                    'password' => $password,
                ]);

            if (! $response->successful() || ! $response->json('access_token')) {
                session(['supabase_auth_error' => 'Email atau kata sandi salah, atau akun belum dikonfirmasi. Periksa email Anda atau gunakan fitur lupa kata sandi.']);
                return false;
            }

            $auth = $response->json();
            $token = $auth['access_token'];
            $profile = Http::withHeaders($this->headers($token))
                ->timeout(15)
                ->get($this->url('/rest/v1/profiles'), [
                    'id' => 'eq.' . $auth['user']['id'],
                    'select' => '*',
                    'limit' => 1,
                ]);

            if (! $profile->successful() || empty($profile->json()[0])) {
                $detail = $profile->json('message')
                    ?? $profile->json('details')
                    ?? $profile->body();

                session([
                    'supabase_auth_error' =>
                        'Supabase Auth berhasil, tetapi pengambilan profil gagal. ' .
                        'HTTP: ' . $profile->status() .
                        ' | User ID: ' . ($auth['user']['id'] ?? 'tidak tersedia') .
                        ' | Detail: ' . substr((string) $detail, 0, 1000)
                ]);

                return false;
            }

            $user->forceFill($profile->json()[0]);
            session([
                'supabase_access_token' => $token,
                'supabase_refresh_token' => $auth['refresh_token'] ?? null,
                'supabase_user_id' => $auth['user']['id'],
            ]);
            session()->forget('supabase_auth_error');

            return true;
        } catch (Throwable $exception) {
            report($exception);
            session(['supabase_auth_error' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba lagi beberapa saat.']);
            return false;
        }
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Password hashes are managed by Supabase Auth.
    }
}
