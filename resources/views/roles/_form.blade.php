@php
  $isEdit = $role->exists;
  $groupLabels = [
    'dashboard' => 'Dashboard', 'books' => 'Buku', 'book-copies' => 'Eksemplar Buku',
    'categories' => 'Kategori', 'authors' => 'Penulis', 'publishers' => 'Penerbit', 'racks' => 'Rak',
    'members' => 'Anggota', 'loans' => 'Peminjaman', 'returns' => 'Pengembalian', 'fines' => 'Denda',
    'reports' => 'Laporan', 'users' => 'User', 'roles' => 'Role', 'permissions' => 'Permission',
    'settings' => 'Pengaturan', 'audit-logs' => 'Audit Log', 'security-dashboard' => 'Security Dashboard',
  ];
  $actionLabels = [
    'view' => 'Lihat', 'view-own' => 'Lihat milik sendiri', 'view-all' => 'Lihat semua',
    'create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus', 'disable' => 'Nonaktifkan',
    'approve' => 'Setujui', 'reject' => 'Tolak', 'handover' => 'Serah terima', 'cancel' => 'Batalkan',
    'process' => 'Proses', 'mark-paid' => 'Tandai lunas', 'waive' => 'Bebaskan', 'export' => 'Export',
    'manage' => 'Kelola',
  ];
@endphp

<div class="mb-4">
  <x-form.input name="name" label="Nama role" :value="$role->name" required :disabled="$isProtected ?? false" help="Huruf kecil, angka, dan tanda hubung saja (contoh: pustakawan-magang)." />
</div>

@if ($isProtected ?? false)
  <div class="p-3 mb-4 text-sm rounded-lg bg-orange-500/10 text-slate-700 dark:text-white">
    <i class="mr-1 text-orange-500 fas fa-exclamation-circle"></i> Role <strong>super-admin</strong> selalu memiliki seluruh permission dan tidak dapat diubah dari sini.
  </div>
@endif

<p class="mb-2 text-sm font-semibold uppercase text-slate-400">Permission</p>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
  @foreach ($permissionGroups as $group => $permissions)
    <div class="p-4 border border-solid rounded-xl border-gray-200 dark:border-white/10">
      <h6 class="mb-2 text-sm dark:text-white">{{ $groupLabels[$group] ?? ucfirst(str_replace('-', ' ', $group)) }}</h6>
      @foreach ($permissions as $permission)
        @php $action = substr($permission->name, strlen($group) + 1); @endphp
        <x-form.checkbox
          name="permissions[]"
          :value="$permission->name"
          :label="$actionLabels[$action] ?? ucfirst(str_replace('-', ' ', $action))"
          :checked="in_array($permission->name, $selected, true)"
          :disabled="$isProtected ?? false"
          class="min-h-6 pl-7 mb-1 block"
        />
      @endforeach
    </div>
  @endforeach
</div>
