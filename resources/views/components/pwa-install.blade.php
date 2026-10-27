<div id="pwa-install" class="hidden fixed right-4 bottom-4 z-50 max-w-[calc(100vw-2rem)]">
  <div class="flex items-center gap-2 p-2 bg-white border border-solid border-gray-200 shadow-lg rounded-xl dark:bg-slate-850 dark:border-white/10">
    <x-button id="pwa-install-button" size="sm" icon="fas fa-download">Pasang Aplikasi</x-button>
    <button id="pwa-install-dismiss" type="button" class="w-11 h-11 text-lg text-slate-500 rounded-lg shrink-0 dark:text-white" aria-label="Tutup tawaran pemasangan">&times;</button>
  </div>
</div>

<dialog id="pwa-install-help" aria-labelledby="pwa-install-title" class="w-full max-w-[min(24rem,calc(100vw-2rem))] p-6 text-slate-700 bg-white border-0 shadow-xl rounded-2xl dark:bg-slate-850 dark:text-white">
  <h2 id="pwa-install-title" class="mb-3 text-lg font-bold">Pasang {{ app_name() }}</h2>
  <p class="mb-4 text-sm leading-relaxed">Buka menu Bagikan di browser, lalu pilih <strong>Tambahkan ke Layar Utama</strong>. Jika pilihan ini belum tersedia, buka website melalui Safari.</p>
  <form method="dialog" class="text-right">
    <x-button type="submit" size="sm">Mengerti</x-button>
  </form>
</dialog>
