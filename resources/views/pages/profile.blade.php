@extends('layouts.base')

@section('title', 'Profil')
@section('page', 'profile')
@section('robots', 'noindex, nofollow')

@php
  $twoFactorEnabled = $user->two_factor_secret !== null;
  $twoFactorConfirmed = $user->hasTwoFactorEnabled();
  $pill = 'z-30 flex items-center justify-center w-full px-0 py-1 mb-0 transition-all ease-in-out border-0 rounded-lg bg-inherit text-slate-700';
@endphp

@section('body')
  <div class="absolute bg-y-50 w-full top-0 bg-[url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg')] min-h-75">
    <span class="absolute top-0 left-0 w-full h-full bg-blue-500 opacity-60"></span>
  </div>

  @include('layouts.partials.sidenav')

  <div class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68">
    @include('layouts.partials.navbar-profile')

    {{-- Header card --}}
    <div class="relative w-full mx-auto mt-60 ">
      <div class="relative flex flex-col flex-auto min-w-0 p-4 mx-6 overflow-hidden break-words bg-white border-0 dark:bg-slate-850 dark:shadow-dark-xl shadow-3xl rounded-2xl bg-clip-border">
        <div class="flex flex-wrap -mx-3">
          <div class="flex-none w-auto max-w-full px-3">
            <div class="relative inline-flex items-center justify-center text-white transition-all duration-200 ease-in-out text-base h-19 w-19 rounded-xl">
              <img src="{{ $user->avatar_url }}" alt="profile_image" class="object-cover w-full h-full shadow-2xl rounded-xl" />
            </div>
          </div>
          <div class="flex-none w-auto max-w-full px-3 my-auto">
            <div class="h-full">
              <h5 class="mb-1 dark:text-white">{{ $user->name }}
                @foreach ($user->getRoleNames() as $roleName)
                  <span class="ml-1 px-2 py-0.5 text-xxs font-bold text-white uppercase rounded-md bg-gradient-to-tl from-blue-500 to-violet-500 align-middle">{{ str_replace('-', ' ', $roleName) }}</span>
                @endforeach
              </h5>
              <p class="mb-0 font-semibold leading-normal dark:text-white dark:opacity-60 text-sm">
                {{ $user->email }}
                @if ($user->hasVerifiedEmail())
                  <i class="ml-1 text-emerald-500 fas fa-check-circle" title="Email terverifikasi"></i>
                @else
                  <span class="ml-1 text-xs font-normal text-orange-500"><i class="fas fa-exclamation-circle"></i> belum terverifikasi</span>
                @endif
              </p>
            </div>
          </div>
          <div class="w-full max-w-full px-3 mx-auto mt-4 sm:my-auto sm:mr-0 md:w-1/2 md:flex-none lg:w-5/12">
            <div class="relative right-0">
              <ul class="relative flex flex-wrap p-1 list-none bg-gray-50 rounded-xl" nav-pills role="tablist">
                <li class="z-30 flex-auto text-center">
                  <a class="{{ $pill }}" nav-link active href="#edit-profile" role="tab" aria-selected="true"><i class="ni ni-single-02"></i><span class="ml-2">Profil</span></a>
                </li>
                <li class="z-30 flex-auto text-center">
                  <a class="{{ $pill }}" nav-link href="#security" role="tab" aria-selected="false"><i class="ni ni-lock-circle-open"></i><span class="ml-2">Keamanan</span></a>
                </li>
                <li class="z-30 flex-auto text-center">
                  <a class="{{ $pill }}" nav-link href="#sessions" role="tab" aria-selected="false"><i class="ni ni-tablet-button"></i><span class="ml-2">Sesi</span></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="w-full p-6 mx-auto">
      @include('layouts.partials.alerts')

      <div class="flex flex-wrap -mx-3">
        <div class="w-full max-w-full px-3 shrink-0 md:w-8/12 md:flex-0">

          {{-- Edit profile (Fortify: user-profile-information.update) --}}
          <form id="edit-profile" method="POST" action="{{ route('user-profile-information.update') }}">
            @csrf
            @method('PUT')
            <x-card title="Edit Profil" class="mb-6">
              <x-slot:actions>
                <x-button type="submit" icon="fas fa-save">Simpan perubahan</x-button>
              </x-slot:actions>

              <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 md:w-6/12">
                  <x-form.input name="name" label="Nama lengkap" :value="$user->name" required icon="fas fa-user" />
                </div>
                <div class="w-full max-w-full px-3 md:w-6/12">
                  <x-form.input name="email" label="Alamat email" type="email" :value="$user->email" required icon="fas fa-envelope" help="Mengubah email memerlukan verifikasi ulang." />
                </div>
              </div>
            </x-card>
          </form>

          {{-- Change password (Fortify: user-password.update, error bag "updatePassword") --}}
          <form id="security" method="POST" action="{{ route('user-password.update') }}">
            @csrf
            @method('PUT')
            <x-card title="Ubah Kata Sandi" class="mb-6">
              <x-slot:actions>
                <x-button type="submit" variant="dark" icon="fas fa-key">Perbarui kata sandi</x-button>
              </x-slot:actions>
              <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 md:w-4/12">
                  <x-form.input name="current_password" label="Kata sandi saat ini" type="password" bag="updatePassword" autocomplete="current-password" password-toggle />
                </div>
                <div class="w-full max-w-full px-3 md:w-4/12">
                  <x-form.input name="password" label="Kata sandi baru" type="password" bag="updatePassword" autocomplete="new-password" password-toggle help="Minimal 8 karakter." />
                </div>
                <div class="w-full max-w-full px-3 md:w-4/12">
                  <x-form.input name="password_confirmation" label="Konfirmasi kata sandi baru" type="password" bag="updatePassword" autocomplete="new-password" />
                </div>
              </div>
            </x-card>
          </form>

          {{-- Two-factor authentication (Fortify: two-factor.*) --}}
          <x-card title="Verifikasi Dua Langkah (2FA)" subtitle="Tambahkan lapisan keamanan dengan aplikasi TOTP (Google Authenticator, Authy, 1Password...)." class="mb-6">
            <x-slot:actions>
              @if ($twoFactorConfirmed)
                <span class="bg-gradient-to-tl from-emerald-500 to-teal-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Aktif</span>
              @elseif ($twoFactorEnabled)
                <span class="bg-gradient-to-tl from-orange-500 to-yellow-500 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Menunggu konfirmasi</span>
              @else
                <span class="bg-gradient-to-tl from-slate-600 to-slate-300 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Nonaktif</span>
              @endif
            </x-slot:actions>

            @if (! $twoFactorEnabled)
              <p class="text-sm dark:text-white/80">Setelah 2FA aktif, Anda akan diminta memasukkan kode acak saat masuk. Ambil kode dari aplikasi authenticator di ponsel Anda.</p>
              <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <x-button type="submit" variant="success" icon="fas fa-shield-alt">Aktifkan 2FA</x-button>
              </form>
            @elseif (! $twoFactorConfirmed)
              <div class="flex flex-wrap -mx-3">
                <div class="w-full max-w-full px-3 md:w-5/12">
                  <div class="inline-block p-2 bg-white border border-solid rounded-xl border-gray-200 [&_svg]:w-full [&_svg]:h-auto">{!! $user->twoFactorQrCodeSvg() !!}</div>
                </div>
                <div class="w-full max-w-full px-3 md:w-7/12">
                  <p class="text-sm dark:text-white/80">Pindai kode QR dengan aplikasi authenticator, atau masukkan kunci ini secara manual:</p>
                  <code class="block px-3 py-2 mb-4 text-sm font-bold break-all rounded-lg bg-gray-100 text-slate-700 dark:bg-slate-900 dark:text-white">{{ $user->two_factor_secret }}</code>
                  <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="flex-1 min-w-40">
                      <x-form.input name="code" label="Kode 6 digit" placeholder="123456" inputmode="numeric" autocomplete="one-time-code" bag="confirmTwoFactorAuthentication" class="mb-0" required />
                    </div>
                    <x-button type="submit" variant="success" icon="fas fa-check" class="mb-0">Konfirmasi &amp; aktifkan</x-button>
                  </form>
                </div>
              </div>
              <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="link" class="text-red-600">Batalkan pengaturan</x-button>
              </form>
            @else
              <p class="text-sm dark:text-white/80">2FA sedang <span class="font-semibold text-emerald-500">aktif</span>. Simpan kode pemulihan berikut di tempat aman — setiap kode hanya dapat dipakai sekali jika Anda kehilangan akses ke aplikasi authenticator.</p>
              <div class="grid grid-cols-1 gap-2 p-4 mb-4 font-mono text-sm rounded-lg sm:grid-cols-2 bg-gray-100 text-slate-700 dark:bg-slate-900 dark:text-white">
                @foreach ($user->recoveryCodes() as $code)
                  <div>{{ $code }}</div>
                @endforeach
              </div>
              <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                  @csrf
                  <x-button type="submit" variant="dark" icon="fas fa-sync">Buat ulang kode pemulihan</x-button>
                </form>
                <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Nonaktifkan verifikasi dua langkah?');">
                  @csrf
                  @method('DELETE')
                  <x-button type="submit" variant="danger" icon="fas fa-times">Nonaktifkan</x-button>
                </form>
              </div>
            @endif
          </x-card>

          {{-- Browser sessions --}}
          <x-card id="sessions" title="Sesi Peramban" subtitle="Kelola dan keluarkan sesi aktif Anda di peramban/perangkat lain." class="mb-6">
            <ul class="flex flex-col pl-0 mb-4 rounded-lg">
              @forelse ($sessions as $session)
                <li class="relative flex items-center py-3 border-b border-solid border-gray-200 dark:border-white/10 last:border-0">
                  <div class="inline-flex items-center justify-center w-10 h-10 mr-4 text-white rounded-xl bg-gradient-to-tl {{ $session->is_current ? 'from-emerald-500 to-teal-400' : 'from-slate-600 to-slate-300' }} shrink-0">
                    <i class="fas {{ $session->device === 'mobile' ? 'fa-mobile-alt' : ($session->device === 'tablet' ? 'fa-tablet-alt' : 'fa-desktop') }}"></i>
                  </div>
                  <div class="flex flex-col">
                    <h6 class="mb-0 text-sm leading-normal text-slate-700 dark:text-white">{{ $session->platform }} · {{ $session->browser }}</h6>
                    <span class="text-xs leading-tight text-slate-400 dark:text-white/80">
                      {{ $session->ip_address }},
                      @if ($session->is_current)
                        <span class="font-semibold text-emerald-500">Perangkat ini</span>
                      @else
                        terakhir aktif {{ $session->last_active }}
                      @endif
                    </span>
                  </div>
                </li>
              @empty
                <li class="py-2 text-sm text-slate-400">Informasi sesi hanya tersedia dengan session driver database.</li>
              @endforelse
            </ul>
            @if ($sessions->count() > 1)
              <form method="POST" action="{{ route('profile.sessions.logout-others') }}" class="flex flex-wrap items-end gap-2">
                @csrf
                <div class="flex-1 min-w-48">
                  <x-form.input name="password" label="Konfirmasi kata sandi untuk keluar dari sesi lain" type="password" bag="logoutOtherSessions" autocomplete="current-password" class="mb-0" required />
                </div>
                <x-button type="submit" variant="dark" icon="fas fa-sign-out-alt">Keluar dari sesi lain</x-button>
              </form>
            @endif
          </x-card>

          {{-- Delete account --}}
          <x-card title="Hapus Akun" subtitle="Menghapus akun secara permanen beserta seluruh datanya. Tindakan ini tidak dapat dibatalkan." class="mb-6 border border-solid border-red-500/30">
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Hapus akun Anda secara permanen?');" class="flex flex-wrap items-end gap-2">
              @csrf
              @method('DELETE')
              <div class="flex-1 min-w-48">
                <x-form.input name="password" label="Konfirmasi kata sandi" type="password" bag="deleteAccount" autocomplete="current-password" class="mb-0" required />
              </div>
              <x-button type="submit" variant="danger" icon="fas fa-trash">Hapus akun saya</x-button>
            </form>
          </x-card>
        </div>

        {{-- Right column: profile card --}}
        <div class="w-full max-w-full px-3 mt-6 shrink-0 md:w-4/12 md:flex-0 md:mt-0">
          <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
            <img class="w-full rounded-t-2xl" src="{{ asset('assets/img/bg-profile.jpg') }}" alt="profile cover image">
            <div class="flex flex-wrap justify-center -mx-3">
              <div class="w-4/12 max-w-full px-3 flex-0 ">
                <div class="mb-6 -mt-6 lg:mb-0 lg:-mt-16">
                  <img class="object-cover w-full border-2 border-white border-solid aspect-square rounded-circle" src="{{ $user->avatar_url }}" alt="profile image">
                </div>
              </div>
            </div>

            <div class="flex-auto p-6 pt-2">
              {{-- Avatar upload --}}
              <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="mb-2">
                @csrf
                <x-form.file name="avatar" accept="image/*" button-text="Pilih foto" help="JPG, PNG, atau WEBP maks 2 MB." class="mb-2" />
                <div class="flex gap-2">
                  <x-button type="submit" variant="info" size="sm" icon="fas fa-upload" block>Unggah foto</x-button>
                  @if ($user->avatar)
                    <x-button type="submit" variant="outline" size="sm" form="remove-avatar" icon="fas fa-times">Hapus</x-button>
                  @endif
                </div>
              </form>
              @if ($user->avatar)
                <form id="remove-avatar" method="POST" action="{{ route('profile.avatar.destroy') }}">@csrf @method('DELETE')</form>
              @endif

              <div class="flex justify-center mt-6">
                <div class="grid text-center">
                  <span class="font-bold dark:text-white text-lg">{{ $stats['activities'] }}</span>
                  <span class="leading-normal dark:text-white text-sm opacity-80">Aktivitas</span>
                </div>
                <div class="grid mx-6 text-center">
                  <span class="font-bold dark:text-white text-lg">{{ $stats['notifications'] }}</span>
                  <span class="leading-normal dark:text-white text-sm opacity-80">Belum dibaca</span>
                </div>
                <div class="grid text-center">
                  <span class="font-bold dark:text-white text-lg">{{ (int) $user->created_at->diffInDays() }}</span>
                  <span class="leading-normal dark:text-white text-sm opacity-80">Hari</span>
                </div>
              </div>

              <div class="mt-6 text-center">
                <h5 class="dark:text-white ">{{ $user->name }}</h5>
                <div class="mt-4 mb-2 font-semibold leading-relaxed text-base dark:text-white/80 text-slate-700">
                  <i class="mr-2 dark:text-white ni ni-email-83"></i>
                  {{ $user->email }}
                </div>
                <div class="dark:text-white/80">
                  <i class="mr-2 dark:text-white ni ni-calendar-grid-58"></i>
                  Bergabung {{ $user->created_at->translatedFormat('d M Y') }}
                </div>
              </div>

              @unless ($user->hasVerifiedEmail())
                <div class="p-3 mt-6 text-sm text-left rounded-lg bg-orange-500/10 text-slate-700 dark:text-white">
                  <p class="mb-2 font-semibold"><i class="mr-1 text-orange-500 fas fa-exclamation-circle"></i> Email belum terverifikasi</p>
                  <p class="mb-2 text-xs">Periksa kotak masuk Anda untuk tautan verifikasi, atau minta kirim ulang.</p>
                  <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-button type="submit" variant="warning" size="sm" icon="fas fa-paper-plane">Kirim ulang email verifikasi</x-button>
                  </form>
                </div>
              @endunless
            </div>
          </div>

          <x-card title="Aktivitas Terbaru">
            <ul class="flex flex-col pl-0 mb-0 rounded-lg">
              @forelse ($recentActivities as $activity)
                <li class="relative flex items-center py-2 mb-1 border-0 text-inherit">
                  <div class="inline-flex items-center justify-center w-8 h-8 mr-3 text-white rounded-xl bg-gradient-to-tl {{ $activity->color }} shrink-0"><i class="{{ $activity->icon }} text-xxs"></i></div>
                  <div class="flex flex-col min-w-0">
                    <h6 class="mb-0 text-sm leading-normal truncate text-slate-700 dark:text-white">{{ $activity->description }}</h6>
                    <span class="text-xs leading-tight text-slate-400 dark:text-white/80">{{ $activity->created_at->diffForHumans() }}</span>
                  </div>
                </li>
              @empty
                <li class="py-2 text-sm text-slate-400">Belum ada aktivitas.</li>
              @endforelse
            </ul>
          </x-card>
        </div>
      </div>

      @include('layouts.partials.footer')
    </div>
  </div>
@endsection
