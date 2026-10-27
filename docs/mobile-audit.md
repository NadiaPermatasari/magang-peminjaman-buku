# Audit Responsivitas Mobile

Audit dan perbaikan dilakukan pada 27 Oktober 2026 menggunakan browser Chromium
dengan database SQLite pengujian terpisah. Data uji mencakup nama aplikasi dan
pengguna yang panjang, 26 berkas, pagination, peminjaman dalam beberapa status,
pengajuan perpanjangan, serta notifikasi.

## Temuan dan Perbaikan

| Area | Temuan | Perbaikan |
|---|---|---|
| Navbar autentikasi | Nama aplikasi panjang melebar pada halaman lupa kata sandi | Nama dapat membungkus; ruang logo dan tautan menyesuaikan layar |
| Header aplikasi | Judul dan tombol berpindah ke baris yang boros ruang | Susunan ringkas, judul dapat membungkus, nama profil dibatasi di desktop |
| Menu notifikasi | Panel keluar dari sisi kiri layar ponsel | Lebar dan posisi dibatasi viewport; tinggi panel dapat digulir |
| Sidebar | Penutupan dan fokus keyboard belum lengkap | Backdrop, tombol tutup, Escape, penahanan fokus, `inert` saat tertutup, dan penguncian gulir latar |
| Gulir tema | Perfect Scrollbar pada halaman/tabel menyembunyikan konten yang melebar | Halaman dan tabel memakai gulir native; plugin dibatasi ke sidebar Windows tanpa pointer sentuh |
| Tabel | Kolom di luar layar kurang mudah ditemukan | Area gulir mandiri, petunjuk geser di ponsel, serta fokus keyboard pada tabel yang melebar |
| Formulir | Kolom dengan minimum width tetap dapat melebihi ruang ponsel | Formulir serah terima, penolakan, perpanjangan, dan profil menumpuk di layar kecil |
| Select pencarian | Select asli yang disembunyikan Tom Select tetap menghasilkan kotak absolut di luar layar | Kotak select asli dibatasi 1×1 piksel dan ditempatkan di awal container |
| Unggah foto | Preview, tombol, dan nama file berebut ruang | Baris dapat membungkus; nama file panjang mendapat baris tersendiri di ponsel |
| Kelola pengguna | Filter role satu baris dan pencarian disembunyikan di ponsel | Filter membungkus dan pencarian tersedia di semua ukuran |
| Kontrol sentuh | Sejumlah tombol terlalu kecil dan field berukuran 14 px | Tombol utama/menu minimal setinggi 44 px dan field ponsel berukuran 16 px |
| PWA | Tombol tutup kecil dan dialog dapat terlalu dekat tepi | Target sentuh lebih besar, batas lebar dialog, dan jarak aman untuk indikator home perangkat |

Padding kartu dan area konten dikurangi pada layar kecil. Teks notifikasi,
pemilihan file, breadcrumb, dan pagination yang disentuh juga disesuaikan ke
Bahasa Indonesia.

## Hasil Verifikasi

- **44 halaman × 7 lebar layar = 308 pemeriksaan**, pada 320, 360, 390, 414,
  768, 1024, dan 1440 piksel. Tidak ada halaman yang melebar horizontal di luar
  viewport pada data uji. Tabel lebar tetap dapat digulir di areanya sendiri.
- **27 pemeriksaan tambahan** untuk halaman anggota pada 320, 390, dan 768
  piksel, termasuk dashboard, katalog, pengajuan, detail peminjaman, profil,
  perpanjangan, dan notifikasi.
- Interaksi yang diperiksa: buka/tutup sidebar, backdrop, Escape, perpindahan
  fokus, penguncian gulir, posisi dan lapisan dropdown, gulir tabel, pencarian
  pengguna, unggah/preview/hapus pilihan foto, nama file panjang, serta
  navigasi pada orientasi landscape.
- Collapse sidebar desktop tetap tersimpan setelah reload. Mengubah ukuran
  dari desktop ke ponsel menutup drawer dan menghapus backdrop.
- Panduan pemasangan iOS disimulasikan pada lebar 320 piksel; dialog tetap di
  dalam viewport. Pemeriksaan kelayakan pemasangan PWA di Chrome tetap lolos.
- Tidak ada kesalahan JavaScript pada sesi pemeriksaan browser.
- `php artisan test`: **133 pengujian lulus, 353 assertion**.
- `npm run test:pwa`: **6 pengujian lulus**.
- `npm run build`, pemeriksaan sintaks JavaScript, dan Laravel Pint berhasil.

Pemeriksaan browser menggunakan emulasi ukuran layar dan pointer sentuh.
Pengujian pada perangkat fisik Safari/iOS dan Android belum dilakukan.

## Pedoman Perubahan Berikutnya

- Gunakan `x-card` dan `x-button` agar padding serta target sentuh konsisten.
- Kolom formulir pada ponsel memakai `w-full min-w-0`; minimum width yang lebih
  besar diberikan mulai breakpoint yang sesuai.
- Bungkus tabel lebar dengan `overflow-x-auto`. `resources/js/responsive.js`
  memberi petunjuk geser dan akses keyboard secara otomatis ketika diperlukan.
- Jangan menutupi masalah layout dengan `overflow-x-hidden` pada body atau
  memasang Perfect Scrollbar pada halaman utama/tabel.
- Nama aplikasi, nama pengguna, email, judul berkas, serta nama file perlu
  diperiksa dengan teks panjang, termasuk saat data terisi dan memiliki halaman
  lanjutan.
- Pertahankan breakpoint desktop sidebar 1200 piksel sesuai konfigurasi Argon.
- Setelah mengubah Blade/CSS/JavaScript, jalankan build, smoke test, dan periksa
  halaman terkait pada layar kecil, tablet, dan desktop.
