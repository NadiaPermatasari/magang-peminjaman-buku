<footer class="py-12">
  <div class="container">
    <div class="flex flex-wrap -mx-3">
      <div class="w-8/12 max-w-full px-3 mx-auto mt-1 text-center flex-0">
        <p class="mb-0 text-slate-400">
          &copy; {{ date('Y') }} {{ app_name() }}{{ setting('institution_name') ? ' — '.setting('institution_name') : '' }}
        </p>
      </div>
    </div>
  </div>
</footer>
