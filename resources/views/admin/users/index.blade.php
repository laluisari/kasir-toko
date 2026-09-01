@extends('layouts.main')

@section('title', 'Manajemen User')
@section('subTitle', 'Kelola pengguna aplikasi')

@section('content')
<div class="card h-100 p-0 radius-12">
    <!-- Card Header -->
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between" style="min-height: 70px;">
        <div class="d-flex align-items-center gap-2">
            <h6 class="text-lg fw-semibold text-primary-light mb-0">👥 Manajemen User</h6>
            <span class="text-secondary-light fw-normal">Kelola pengguna sistem</span>
        </div>
        <button type="button" class="add-btn btn btn-primary-600 radius-8 px-20 py-10 d-flex align-items-center gap-2" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <iconify-icon icon="lucide:plus" class="icon"></iconify-icon>
            User
        </button>
    </div>

    <div class="card-body p-24">
        <!-- Success Alert -->
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-20" role="alert" style="border-radius: 0.75rem; border: 1px solid rgba(34, 197, 94, 0.3);">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:circle-check-fill" class="text-success" style="font-size: 1.25rem;"></iconify-icon>
                <span class="fw-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-20" role="alert" style="border-radius: 0.75rem; border: 1px solid rgba(220, 38, 38, 0.3);">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:circle-x-fill" class="text-danger" style="font-size: 1.25rem;"></iconify-icon>
                <span class="fw-medium">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <!-- Table Section -->
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th class="text-sm text-secondary-light fw-semibold" style="width:48px">#</th>
                        <th class="text-sm text-secondary-light fw-semibold">Nama</th>
                        <th class="text-sm text-secondary-light fw-semibold">Email</th>
                        <th class="text-sm text-secondary-light fw-semibold">Role</th>
                        <th class="text-sm text-secondary-light fw-semibold">Dibuat</th>
                        <th class="text-sm text-secondary-light fw-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='rgba(0,0,0,0.02)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td class="text-sm text-secondary-light">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td class="text-sm text-secondary-light fw-medium">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="w-32-px h-32-px bg-primary-100 rounded-circle d-flex justify-content-center align-items-center">
                                        <span class="text-primary-600 fw-bold" style="font-size: 0.875rem;">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="text-sm text-secondary-light">{{ $user->email }}</td>
                            <td class="text-sm text-secondary-light">
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @else
                                    <span class="badge bg-success">Kasir</span>
                                @endif
                            </td>
                            <td class="text-sm text-secondary-light">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-8">
                                    <!-- Edit Button -->
                                    <button type="button" data-user="{{ json_encode($user) }}" class="edit-btn d-flex align-items-center justify-content-center" 
                                            style="width: 36px; height: 36px; border-radius: 0.5rem; background-color: #f0fdf4; border: 1.5px solid #bbf7d0; color: #059669; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                                            onmouseover="this.style.backgroundColor='#059669'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(5, 150, 105, 0.3)';"
                                            onmouseout="this.style.backgroundColor='#f0fdf4'; this.style.color='#059669'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                                        <iconify-icon icon="lucide:edit" class="icon" style="font-size: 1.125rem;"></iconify-icon>
                                    </button>

                                    <!-- Delete Button -->
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="d-flex align-items-center justify-content-center" 
                                                style="width: 36px; height: 36px; border-radius: 0.5rem; background-color: #fef2f2; border: 1.5px solid #fecaca; color: #dc2626; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 0; margin: 0;"
                                                onmouseover="this.style.backgroundColor='#dc2626'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(220, 38, 38, 0.3)';"
                                                onmouseout="this.style.backgroundColor='#fef2f2'; this.style.color='#dc2626'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                                            <iconify-icon icon="lucide:trash-2" class="icon" style="font-size: 1.125rem;"></iconify-icon>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary-light py-40">
                                <iconify-icon icon="lucide:users" class="d-block mx-auto mb-8" style="font-size:2.5rem; opacity:0.3;"></iconify-icon>
                                <p class="mb-0">Belum ada data user.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="d-flex justify-content-end mt-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal untuk Add -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border-radius: 1rem; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom-0 bg-base pt-20 px-24 pb-8">
                <h5 class="modal-title fw-semibold text-primary-light" id="addModalLabel">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-24 py-24" style="background-color: #fafbfc;">
                <form id="addForm" action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="mb-20">
                        <label for="add-name" class="form-label fw-semibold text-sm text-primary-light mb-8">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" placeholder="Masukkan nama lengkap" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-20">
                        <label for="add-email" class="form-label fw-semibold text-sm text-primary-light mb-8">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add-email" name="email" placeholder="Masukkan email" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-20">
                        <label for="add-password" class="form-label fw-semibold text-sm text-primary-light mb-8">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="add-password" name="password" placeholder="Masukkan password (min 6 karakter)" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-20">
                        <label for="add-password_confirmation" class="form-label fw-semibold text-sm text-primary-light mb-8">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="add-password_confirmation" name="password_confirmation" placeholder="Konfirmasi password" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-24">
                        <label for="add-role" class="form-label fw-semibold text-sm text-primary-light mb-8">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="add-role" name="role" required
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin - Admin System</option>
                            <option value="kasir">Kasir - Kasir/POS</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-600 w-100 radius-8" 
                            style="padding: 0.6rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);"
                            onmouseover="this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)';"
                            onmouseout="this.style.boxShadow='0 2px 8px rgba(59, 130, 246, 0.2)';">
                        Simpan User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border-radius: 1rem; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom-0 bg-base pt-20 px-24 pb-8">
                <h5 class="modal-title fw-semibold text-primary-light" id="editModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-24 py-24" style="background-color: #fafbfc;">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-20">
                        <label for="edit-name" class="form-label fw-semibold text-sm text-primary-light mb-8">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" placeholder="Masukkan nama lengkap" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-20">
                        <label for="edit-email" class="form-label fw-semibold text-sm text-primary-light mb-8">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit-email" name="email" placeholder="Masukkan email" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-20">
                        <label for="edit-password" class="form-label fw-semibold text-sm text-primary-light mb-8">Password <span class="text-secondary-light">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" class="form-control" id="edit-password" name="password" placeholder="Masukkan password baru (min 6 karakter)" 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-20">
                        <label for="edit-password_confirmation" class="form-label fw-semibold text-sm text-primary-light mb-8">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="edit-password_confirmation" name="password_confirmation" placeholder="Konfirmasi password baru" 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <div class="mb-24">
                        <label for="edit-role" class="form-label fw-semibold text-sm text-primary-light mb-8">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-role" name="role" required
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin - Admin System</option>
                            <option value="kasir">Kasir - Kasir/POS</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-600 w-100 radius-8" 
                            style="padding: 0.6rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);"
                            onmouseover="this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)';"
                            onmouseout="this.style.boxShadow='0 2px 8px rgba(59, 130, 246, 0.2)';">
                        Perbarui User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(".add-btn").on("click", function() {
        $('#addForm')[0].reset();
        $('#addModal').modal('show');
    });

    $(".edit-btn").on("click", function() {
        var user = $(this).data('user');
        console.log(user);

        $('#edit-name').val(user.name);
        $('#edit-email').val(user.email);
        $('#edit-role').val(user.role);
        $('#edit-password').val('');
        $('#edit-password_confirmation').val('');

        // Update action form dengan ID yang sesuai
        var formAction = "{{ route('users.update', ':id') }}".replace(':id', user.id);
        $('#editForm').attr('action', formAction);

        // Tampilkan modal
        $('#editModal').modal('show');
    });
</script>
@endsection
