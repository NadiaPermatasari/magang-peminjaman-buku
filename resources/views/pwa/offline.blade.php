<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="theme-color" content="{{ setting('primary_color') }}" />
    <title>Koneksi Terputus | {{ app_name() }}</title>
    {{-- Inline styles keep this page usable without Vite, a CDN, or a network. --}}
    <style>
      :root { --color-primary: {{ \App\Support\Color::hexToRgbTriplet(setting('primary_color')) }}; color-scheme: light dark; }
      * { box-sizing: border-box; }
      body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: system-ui, sans-serif; background: #f8fafc; color: #334155; }
      main { width: 100%; max-width: 440px; padding: 36px 28px; text-align: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 12px 40px #0f172a0d; }
      .icon { width: 72px; height: 72px; display: grid; place-items: center; margin: 0 auto 24px; border-radius: 20px; background: rgb(var(--color-primary) / .1); color: rgb(var(--color-primary)); }
      .app-name { margin: 0 0 12px; font-size: 14px; color: #64748b; }
      h1 { margin: 0 0 16px; font-size: 26px; }
      p { margin: 0 0 24px; line-height: 1.7; }
      a { display: block; padding: 14px 20px; border-radius: 10px; background: rgb(var(--color-primary)); color: #fff; font-weight: 600; text-decoration: none; }
      a:focus-visible { outline: 3px solid rgb(var(--color-primary)); outline-offset: 4px; }
      .hint { margin: 20px 0 0; font-size: 13px; color: #64748b; }
      @media (prefers-color-scheme: dark) {
        body { background: #0f172a; color: #f1f5f9; }
        main { background: #1e293b; border-color: #334155; }
        .app-name, .hint { color: #cbd5e1; }
      }
    </style>
  </head>
  <body>
    <main>
      <div class="icon" aria-hidden="true">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
          <path d="M3 3l18 18M8.5 16.5a5 5 0 0 1 7 0M5 12.5a10 10 0 0 1 2-1.5m4-1a10 10 0 0 1 8 2.5M2 8.5a15 15 0 0 1 2-1.5m4-1.5a15 15 0 0 1 14 3" />
          <circle cx="12" cy="20" r="1" fill="currentColor" stroke="none" />
        </svg>
      </div>
      <p class="app-name">{{ app_name() }}</p>
      <h1>Koneksi terputus</h1>
      <p>Periksa koneksi internet Anda, lalu coba lagi untuk melanjutkan akses ke layanan peminjaman berkas.</p>
      <a href="">Coba Lagi</a>
      <p class="hint">Data anggota dan transaksi hanya dapat diakses saat terhubung ke internet.</p>
    </main>
  </body>
</html>
