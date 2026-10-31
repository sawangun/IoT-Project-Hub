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

    /**
     * In localhost development, optionally auto-confirm an old account before
     * retrying password sign-in. The service-role key is server-side only.
     */
    private function autoConfirmExistingUser(string $email): bool
    {
        $serviceKey = (string) config('services.supabase.service_role_key');
        if (! app()->environment('local') || ! config('services.supabase.auto_confirm') || $serviceKey === '') {
            return false;
        }

        try {
            // GoTrue Admin API returns a paginated list; search exact email locally.
            for ($page = 1; $page <= 10; $page++) {
                $list = Http::withHeaders($this->adminHeaders())
                    ->timeout(15)
                    ->get($this->url('/auth/v1/admin/users'), [
                        'page' => $page,
                        'per_page' => 100,
                    ]);

                if (! $list->successful()) {
                    return false;
                }

                $users = $list->json('users', []);
                foreach ($users as $candidate) {
                    if (isset($candidate['id']) && strcasecmp((string) ($candidate['email'] ?? ''), $email) === 0) {
                        $updated = Http::withHeaders($this->adminHeaders())
                            ->timeout(15)
                            ->put($this->url('/auth/v1/admin/users/' . $candidate['id']), [
                                'email_confirm' => true,
                            ]);

                        return $updated->successful();
                    }
                }

                if (count($users) < 100) {
                    break;
                }
            }
        } catch (Throwable) {
            return false;
        }

        return false;
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

            if (! $response->successful() && $this->isEmailNotConfirmed($response) && $this->autoConfirmExistingUser($email)) {
                $response = Http::withHeaders($this->headers())
                    ->timeout(20)
                    ->post($this->url('/auth/v1/token?grant_type=password'), [
                        'email' => $email,
                        'password' => $password,
                    ]);
            }

            if (! $response->successful() || ! $response->json('access_token')) {
                $message = strtolower((string) ($response->json('msg') ?? $response->json('message') ?? $response->json('error_description') ?? ''));
                session(['supabase_auth_error' => str_contains($message, 'email not confirmed') || str_contains($message, 'email_not_confirmed')
                    ? 'Akun masih belum dikonfirmasi. Untuk auto-confirm akun lama, isi SUPABASE_SERVICE_ROLE_KEY di .env lokal.'
                    : 'Email atau kata sandi salah. Detail dari Supabase: ' . ($response->json('msg') ?? $response->json('message') ?? 'Autentikasi ditolak.')]);
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
            session(['supabase_auth_error' => 'Tidak dapat menghubungi Supabase: ' . $exception->getMessage()]);
            return false;
        }
    }

    private function isEmailNotConfirmed($response): bool
    {
        $message = strtolower(implode(' ', [
            (string) $response->json('msg'),
            (string) $response->json('message'),
            (string) $response->json('error_description'),
            (string) $response->json('code'),
        ]));

        return str_contains($message, 'email_not_confirmed') || str_contains($message, 'email not confirmed');
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Password hashes are managed by Supabase Auth.
    }
}
