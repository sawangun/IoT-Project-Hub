<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  // Akun dan profil dibuat melalui Supabase Auth.
  // Buat admin: daftar akun biasa, lalu SQL Editor:
  // update public.profiles set role = 'admin' where email = 'email-admin@domain.ac.id';
 }
}
