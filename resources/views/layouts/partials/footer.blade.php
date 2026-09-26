<footer class="pt-4">
  <div class="w-full px-6 mx-auto">
    <div class="flex flex-wrap items-center -mx-3 lg:justify-between">
      <div class="w-full max-w-full px-3 mt-0 mb-6 shrink-0 lg:mb-0 lg:w-1/2 lg:flex-none">
        <div class="text-sm leading-normal text-center text-slate-500 lg:text-left">
          © {{ date('Y') }} {{ app_name() }}, {{ setting('footer_text') }}
        </div>
      </div>
      <div class="w-full max-w-full px-3 mt-0 shrink-0 lg:w-1/2 lg:flex-none">
        <ul class="flex flex-wrap justify-center pl-0 mb-0 list-none lg:justify-end">
          @if (setting('email'))
            <li class="nav-item">
              <a href="mailto:{{ setting('email') }}" class="block px-4 pt-0 pb-1 text-sm font-normal transition-colors ease-in-out text-slate-500">{{ setting('email') }}</a>
            </li>
          @endif
          @if (setting('phone'))
            <li class="nav-item">
              <span class="block px-4 pt-0 pb-1 text-sm font-normal text-slate-500">{{ setting('phone') }}</span>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</footer>
