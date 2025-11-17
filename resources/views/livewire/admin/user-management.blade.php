<x-slot:breadcrumb>{{ $breadcrumb }}</x-slot>

<div>
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    @error('save')
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ $message }}
    </div>
    @enderror

    <!-- Header -->
    <div class="p-4 mb-6 bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-3xl font-bold text-heading">{{ $title }}</h3>
            <button wire:click="openCreateModal"
                    class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
                + Tambah
            </button>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Search -->
            <div>
                <input wire:model.live.debounce.300ms="search"
                       type="text" id="input-group-1"
                       class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                       placeholder="Cari nama atau email...">
            </div>
            <!-- Role Filter -->
            <div>
                <select
                    wire:model.live="roleFilter"
                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                >
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <!-- Per Page -->
{{--            <div>--}}
{{--                <select--}}
{{--                    wire:model.live="perPage"--}}
{{--                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"--}}
{{--                >--}}
{{--                    <option value="5">5 per halaman</option>--}}
{{--                    <option value="10">10 per halaman</option>--}}
{{--                    <option value="25">25 per halaman</option>--}}
{{--                    <option value="50">50 per halaman</option>--}}
{{--                </select>--}}
{{--            </div>--}}
        </div>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left rtl:text-right text-body border-b">
            <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($users as $user)
                <tr wire:key="user-{{ $user->id }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button
                            wire:click="openEditModal({{ $user->id }})"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            Edit
                        </button>
                        @if(auth()->id() !== $user->id)
                            <button
                                wire:click="delete({{ $user->id }})"
                                wire:confirm="Yakin ingin menghapus user ini?"
                                class="text-red-600 hover:text-red-900"
                            >
                                Hapus
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data user
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-500/50 flex items-center justify-center z-50"
             wire:click.self="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">
                            {{ $modalMode === 'create' ? 'Tambah User Baru' : 'Edit User' }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">Nama</label>
                            <input
                                wire:model="name"
                                type="text" id="name" name="name"
                                class="border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                                placeholder="Masukan nama"
                            />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                Email
                            </label>
                            <input wire:model="email"
                                   type="email" id="email" name="email"
                                   class="border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                                   placeholder="Masukan email"
                            />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                Password {{ $modalMode === 'edit' ? '(kosongkan jika tidak ingin mengubah)' : '' }}
                            </label>
                            <input
                                wire:model="password"
                                type="password" id="password" name="password"
                                class="border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                                placeholder="Masukan password"
                            />
                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                Konfirmasi Password
                            </label>
                            <input
                                wire:model="password_confirmation"
                                type="password" id="password_confirmation" name="password_confirmation"
                                class="border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                                placeholder="Konfirmasi password"/>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                Role
                            </label>
                            <select
                                wire:model="role"
                                class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                            >
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status -->
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <input
                                    wire:model="is_active"
                                    type="checkbox"
                                    checked id="checked-checkbox"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                >
                                <label for="remember" class="ml-2 block text-sm text-gray-900">
                                    Pengguna Aktif
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-2 pt-4">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 flex items-center"
                            >
                                <span wire:loading.remove wire:target="save">Simpan</span>
                                <span wire:loading wire:target="save">Loading...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
