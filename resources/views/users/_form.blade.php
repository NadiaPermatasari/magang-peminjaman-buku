{{-- Shared user form fields. Expects $user (may be a new User instance) and $roles (assignable role => label map). --}}
@php
  $isEdit = $user->exists;
  $isSelf = $isEdit && $user->id === auth()->id();
  $currentRole = $user->getRoleNames()->first();
@endphp

<p class="leading-normal uppercase dark:text-white dark:opacity-60 text-sm">Akun</p>
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="name" label="Nama lengkap" :value="$user->name" required />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="email" label="Alamat email" type="email" :value="$user->email" required icon="fas fa-envelope" />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.select name="role" label="Role" :options="$roles" :selected="$currentRole" required :disabled="$isSelf" help="{{ $isSelf ? 'Anda tidak dapat mengubah role Anda sendiri.' : 'Menentukan permission yang dimiliki akun ini.' }}" />
    @if ($isSelf)
      <input type="hidden" name="role" value="{{ $currentRole }}" />
    @endif
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.toggle name="verified" label="Email terverifikasi" :checked="$isEdit ? (bool) $user->email_verified_at : true" help="Nonaktifkan untuk mewajibkan verifikasi email." />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="password" type="password" :label="$isEdit ? 'Kata sandi baru (kosongkan jika tidak diubah)' : 'Kata sandi'" :required="! $isEdit" password-toggle autocomplete="new-password" help="Minimal 8 karakter." />
  </div>
  <div class="w-full max-w-full px-3 md:w-6/12">
    <x-form.input name="password_confirmation" type="password" label="Konfirmasi kata sandi" :required="! $isEdit" autocomplete="new-password" />
  </div>
</div>
