# Keamanan Aplikasi

Baseline: OWASP ASVS Level 2 (spec §57). Setiap kontrol di bawah ini adalah implementasi nyata di kode, bukan sekadar checklist.

## Broken Access Control / IDOR

- Setiap route sensitif digerbangi **middleware permission** (lapis pertama) **dan Policy** (lapis kedua, object-level) — lihat `app/Policies/*`.
- Pola IDOR eksplisit di `LoanPolicy`, `LoanExtensionPolicy`, `MemberPolicy`: `$user->can('...view-all')` atau `$resource->member->user_id === $user->id`.
- UUID (`App\Support\Concerns\HasUuid`) dipakai di semua URL publik, **tapi bukan pengganti otorisasi** — resource acak menghasilkan 404 (tidak ditemukan lewat route-model-binding), resource milik orang lain menghasilkan 403 (ditolak Policy). Dites di `tests/Feature/IdorTest.php`.
- Tidak ada Gate::before bypass global untuk super-admin — super-admin lolos karena permission-nya memang lengkap (di-seed eksplisit), bukan lewat jalan pintas yang melewati Policy (lihat `AppServiceProvider::boot()`).

## Autentikasi

- Laravel Fortify: login, logout, reset password, verifikasi email, konfirmasi password, 2FA (TOTP).
- Registrasi publik **dinonaktifkan** (`config/fortify.php` — fitur `registration()` sengaja tidak diaktifkan) — default aman spec §58.
- 2FA (TOTP) bersifat opsional untuk semua role — keputusan produk (bukan default spec §6, yang aslinya mewajibkan 2FA untuk staf). Pengguna dapat mengaktifkan/menonaktifkannya sendiri kapan saja lewat halaman Profil; tidak ada middleware yang memblokir akses karena 2FA belum aktif.
- Password di-hash `bcrypt` (`'password' => 'hashed'` cast) — tidak pernah disimpan/dibandingkan plaintext.
- Aksi sensitif (ubah role, disable user, regenerate recovery code, disable 2FA, ubah Pengaturan) memerlukan `password.confirm` — middleware ini digerbangi di rute **GET (tampilkan form) dan POST/PUT** sekaligus, supaya konfirmasi terjadi sebelum form diisi (menghindari data hilang akibat redirect konfirmasi yang tidak mempertahankan body POST).

## Enkripsi Data (spec §11)

| Field | Perlakuan |
|---|---|
| Password (users) | Hash (`bcrypt`), tidak reversibel |
| `two_factor_secret`, `two_factor_recovery_codes` | Dienkripsi oleh Fortify sendiri (`Fortify::currentEncrypter()`, default `APP_KEY`) — **tidak** diberi cast Eloquent tambahan (lihat `docs/architecture.md` untuk alasannya) |
| `members.phone`, `members.address`, `members.identity_number` | Eloquent cast `encrypted` (`APP_KEY`) |
| `members.identity_number_index` | Blind index (keyed-HMAC, `BLIND_INDEX_KEY` — kunci terpisah dari `APP_KEY`) via `App\Support\BlindIndex`, dipakai untuk cek duplikat NIK tanpa menyimpan nilai yang mudah ditebak (bukan SHA-256 polos) |
| Email (users/members) | **Tidak** dienkripsi (dibutuhkan untuk lookup login/unik) — sesuai spec §11 yang bersyarat |

`APP_KEY`, `BLIND_INDEX_KEY`, dan `DB_PASSWORD` adalah tiga secret terpisah (spec §11) — jangan pernah disamakan.

## Injection

- Seluruh query lewat Eloquent/Query Builder dengan parameter binding.
- Satu-satunya `DB::statement`/`selectRaw` yang ada (`ReportController::statistics`, loan code generator) menggunakan ekspresi tetap per-driver (bukan menyisipkan input user) dan tidak pernah menggabungkan string dari request.

## XSS

- Semua output Blade pakai `{{ }}` (auto-escape). Tidak ada `{!! !!}` untuk konten yang berasal dari input user di seluruh aplikasi.
- Content-Security-Policy aktif lewat `SecurityHeaders` middleware (lihat bawah) sebagai lapis pertahanan tambahan.

## CSRF

- Middleware `web` (termasuk `VerifyCsrfToken`) aktif di seluruh route state-changing — didapat otomatis karena seluruh route terdaftar lewat `routes/web.php`. Tidak ada route yang dikecualikan dari CSRF.
- Diverifikasi struktural di `tests/Feature/SecurityTest.php` (CSRF di-bypass otomatis oleh Laravel saat menjalankan test — lihat komentar di file tersebut untuk penjelasan kenapa pengujian dilakukan secara struktural, bukan dengan memicu 419 secara dinamis).

## Mass Assignment

- Tidak ada controller yang memanggil `Model::create($request->all())`. Semua input lewat FormRequest (`$request->validated()`/`$request->safe()`).
- Field sensitif (`role`, `is_admin`, `permissions`, `status` approval, dll) hanya diset lewat pemanggilan eksplisit di controller (mis. `$user->syncRoles([...])`), tidak pernah dari array request mentah.

## Upload File (spec §22)

- MIME divalidasi dari isi file (`mimes:`), bukan ekstensi yang dikirim klien.
- Ukuran dibatasi per jenis file.
- Nama file diacak otomatis oleh `Storage::store()` (Laravel) — nama asli dari klien tidak pernah dipercaya/dipakai sebagai path.
- SVG tidak termasuk daftar MIME yang diizinkan di mana pun (sampul buku, avatar, branding) — mencegah SVG berisi script.
- File privat tidak disimpan di `public/` langsung — semua lewat disk `public` (symlink terkontrol via `storage:link`).

## Rate Limiting (spec §32)

| Endpoint | Limiter |
|---|---|
| Login | 5/menit per (email+IP), bawaan Fortify |
| 2FA challenge | 5/menit per sesi login, bawaan Fortify |
| Forgot/reset password, verifikasi email, seluruh route Fortify lain | 10/menit per IP (limiter `password-reset`, ditambahkan karena Fortify sendiri tidak men-throttle `password.email`/`password.update`) |
| Lookup barcode (`book-copies.lookup`) | 60/menit per user (limiter `search`) |
| Upload (avatar, sampul buku, branding Pengaturan) | 20/menit per user (limiter `upload`) |

Tidak ada permanent account lockout — sesuai spec §32, mencegah limiter ini disalahgunakan untuk men-DoS akun orang lain.

## Session Security (spec §33)

- `http_only=true`, `same_site=lax` (default `config/session.php`).
- `SESSION_SECURE_COOKIE=true` **wajib diaktifkan manual** saat production di-serve lewat HTTPS (lihat `.env.example`).
- Session diregenerasi saat login (bawaan Fortify) dan diinvalidasi saat logout.
- `AuthenticateSession` middleware aktif — mengubah password otomatis membatalkan sesi lain.
- "Keluar dari sesi lain" tersedia di halaman Profil (`Auth::logoutOtherDevices()`).

## Security Headers (spec §34)

Middleware `SecurityHeaders` (global, semua response): `Content-Security-Policy` (termasuk `frame-ancestors 'self'` untuk clickjacking), `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, dan `Strict-Transport-Security` otomatis saat request HTTPS.

## Error Handling (spec §35)

- Production wajib `APP_DEBUG=false` — tidak ada stack trace/path/SQL/credential yang bocor ke pengguna.
- Halaman error generik sudah tersedia (`resources/views/errors/*`).

## Audit Trail (spec §24)

- Tabel `activity_logs`, append-only — tidak ada route/tombol update atau delete di UI manapun.
- `App\Support\Activity::log()` **tidak pernah** dipanggil dengan password/OTP/2FA secret/recovery code/token — hanya deskripsi teks dan before/after value non-sensitif (mis. perubahan role, bukan perubahan password).
- Terlihat di menu Administrasi > Audit Log (`audit-logs.view`) dan diringkas di Security Dashboard (`security-dashboard.view`, spec §25).

## Database & Redis (spec §36/§37)

Tidak diatur dari kode aplikasi (tanggung jawab infrastruktur) — didokumentasikan di README bagian *Deployment Produksi*: gunakan akun DB khusus aplikasi (bukan `root`), least-privilege, tidak diekspos publik; Redis (bila dipakai untuk cache/queue) hanya di jaringan privat.

## Dependency Security (spec §38)

`composer audit` dan `npm audit` dijalankan sebelum setiap rilis — lihat hasil terbaru di laporan penyelesaian proyek. Tidak ada dependency yang di-*major upgrade* otomatis tanpa peninjauan.
