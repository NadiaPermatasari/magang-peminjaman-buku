{{-- Flash messages (session success / status / error / warning) and validation errors, Argon alert style. --}}
@php
  // Fortify flashes short status keys; translate them into readable messages.
  $statusMessages = [
    'profile-information-updated' => 'Profil berhasil diperbarui.',
    'password-updated' => 'Kata sandi berhasil diubah.',
    'verification-link-sent' => 'Tautan verifikasi baru telah dikirim ke email Anda.',
    'two-factor-authentication-enabled' => 'Verifikasi dua langkah dimulai — pindai kode QR dan konfirmasi dengan kode untuk mengaktifkannya.',
    'two-factor-authentication-confirmed' => 'Verifikasi dua langkah kini aktif. Simpan kode pemulihan Anda!',
    'two-factor-authentication-disabled' => 'Verifikasi dua langkah telah dinonaktifkan.',
    'recovery-codes-generated' => 'Kode pemulihan baru telah dibuat.',
  ];
  $status = session('success') ?? session('status');
  $status = $statusMessages[$status] ?? $status;
@endphp

@if ($status)
  <div class="relative flex items-center p-4 mb-4 text-sm text-white bg-gradient-to-tl from-emerald-500 to-teal-400 rounded-lg shadow-md" role="alert">
    <i class="mr-2 fas fa-check-circle"></i>
    <span class="font-semibold">{{ $status }}</span>
  </div>
@endif

@if (session('warning'))
  <div class="relative flex items-center p-4 mb-4 text-sm text-white bg-gradient-to-tl from-orange-500 to-yellow-500 rounded-lg shadow-md" role="alert">
    <i class="mr-2 fas fa-exclamation-circle"></i>
    <span class="font-semibold">{{ session('warning') }}</span>
  </div>
@endif

@if (session('error'))
  <div class="relative flex items-center p-4 mb-4 text-sm text-white bg-gradient-to-tl from-red-600 to-orange-600 rounded-lg shadow-md" role="alert">
    <i class="mr-2 fas fa-exclamation-circle"></i>
    <span class="font-semibold">{{ session('error') }}</span>
  </div>
@endif

@if ($errors->any())
  <div class="relative p-4 mb-4 text-sm text-white bg-gradient-to-tl from-red-600 to-orange-600 rounded-lg shadow-md" role="alert">
    <p class="mb-1 font-semibold"><i class="mr-2 fas fa-exclamation-triangle"></i>Mohon perbaiki kesalahan berikut:</p>
    <ul class="pl-6 mb-0 list-disc">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
