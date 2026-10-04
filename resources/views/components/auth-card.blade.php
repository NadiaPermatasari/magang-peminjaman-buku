{{--
  Auth page shell (sign-in style): form card on the left, illustration on the right.
  <x-auth-card heading="Sign In" subheading="Enter your email and password">
    ...form...
    <x-slot:footer>links</x-slot:footer>
  </x-auth-card>
--}}
@props(['heading', 'subheading' => null])

<section>
  <div class="relative flex items-center min-h-screen p-0 overflow-hidden bg-center bg-cover">
    <div class="container z-1">
      <div class="flex flex-wrap -mx-3">
        <div class="flex flex-col w-full max-w-full px-3 mx-auto lg:mx-0 shrink-0 md:flex-0 md:w-7/12 lg:w-5/12 xl:w-4/12">
          <div class="relative flex flex-col min-w-0 break-words bg-transparent border-0 shadow-none lg:py4 dark:bg-gray-950 rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0">
              <h4 class="font-bold">{{ $heading }}</h4>
              <p class="mb-0">{{ $subheading }}</p>
            </div>
            <div class="flex-auto p-6">
              @if (session('status'))
                <div class="p-3 mb-4 text-sm text-white bg-gradient-to-tl from-emerald-500 to-teal-400 rounded-lg" role="alert">{{ session('status') }}</div>
              @endif
              {{ $slot }}
            </div>
            @isset($footer)
              <div class="border-black/12.5 rounded-b-2xl border-t-0 border-solid p-6 text-center pt-0 px-1 sm:px-6">
                {{ $footer }}
              </div>
            @endisset
          </div>
        </div>
        @php
          $loginBackground = setting('login_background')
            ? asset('storage/'.setting('login_background'))
            : 'https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/signin-ill.jpg';
        @endphp
        <div class="absolute top-0 right-0 flex-col justify-center hidden w-7/12 h-full max-w-full px-3 pr-0 my-auto text-center flex-0 lg:flex">
          <div class="relative flex flex-col justify-center h-full bg-center bg-cover px-24 m-4 overflow-hidden rounded-xl" style="background-image: url('{{ $loginBackground }}')">
            <span class="absolute top-0 left-0 w-full h-full bg-center bg-cover bg-gradient-to-tl from-blue-500 to-violet-500 opacity-60"></span>
            <h4 class="z-20 mt-12 font-bold text-white">"Pengetahuan adalah jendela dunia"</h4>
            <p class="z-20 text-white">Kelola koleksi, keanggotaan, dan peminjaman perpustakaan Anda dalam satu sistem.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
