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
                    Tambah Transaksi
                </button>
            </div>

            <!-- Type Tabs -->
            <div class="mb-4 border-b border-gray-200">
                <nav class="-mb-px flex space-x-4">
                    <button wire:click="$set('typeFilter', '')"
                        class="px-4 py-2 border-b-2 font-medium text-sm {{ $typeFilter === '' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Semua Transaksi
                        </div>
                    </button>
                    <button wire:click="$set('typeFilter', 'in')"
                        class="px-4 py-2 border-b-2 font-medium text-sm {{ $typeFilter === 'in' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m0 0l-4-4m4 4l4-4" />
                            </svg>
                            Stok Masuk
                        </div>
                    </button>
                    <button wire:click="$set('typeFilter', 'out')"
                        class="px-4 py-2 border-b-2 font-medium text-sm {{ $typeFilter === 'out' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                            </svg>
                            Stok Keluar
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-heading">Pencarian</label>
                    <input wire:model.live.debounce.300ms="search" type="text" name="search"
                        class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                        placeholder="Cari kode, produk, atau supplier...">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-heading">Dari Tanggal</label>
                    <input wire:model.live="dateFrom" type="date" name="dateFrom"
                        class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-heading">Sampai Tanggal</label>
                    <input wire:model.live="dateTo" type="date" name="dateTo"
                        class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
            <table class="w-full text-sm text-left rtl:text-right text-body border-b">
                <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode
                            Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kuantitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stockTransactions as $stockTransaction)
                        <tr wire:key="stockTransaction-{{ $stockTransaction->id }}" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loop->iteration + ($stockTransactions->currentPage() - 1) * $stockTransactions->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $stockTransaction->transaction_code }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $stockTransaction->user?->name ?? 'System' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stockTransaction->transaction_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $stockTransaction->product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $stockTransaction->product->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stockTransaction->supplier->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stockTransaction->type->value === 'in')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m0 0l-4-4m4 4l4-4" />
                                        </svg>
                                        Stok Masuk
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 20V4m0 0l4 4m-4-4l-4 4" />
                                        </svg>
                                        Stok Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stockTransaction->type->value === 'in')
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-2xl font-bold text-green-600">+{{ number_format($stockTransaction->quantity) }}</span>
                                        <span class="text-xs text-gray-500">unit</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-2xl font-bold text-red-600">-{{ number_format($stockTransaction->quantity) }}</span>
                                        <span class="text-xs text-gray-500">unit</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="openEditModal({{ $stockTransaction->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $stockTransaction->id }})"
                                    wire:confirm="Yakin ingin menghapus data stok ini?"
                                    class="text-red-600 hover:text-red-900">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada transaksi stok</p>
                                    <p class="text-sm mt-1">Silakan tambah transaksi stok baru</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4">
                {{ $stockTransactions->links() }}
            </div>
        </div>

        <!-- Modal -->
        @if($showModal)
            <div class="fixed inset-0 bg-gray-500/50 flex items-center justify-center z-50" wire:click.self="closeModal">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">
                                {{ $modalMode === 'create' ? 'Tambah Stok Baru' : 'Edit Stok' }}
                            </h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form wire:submit="save" class="space-y-4">
                            <!-- Product -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Produk</label>
                                <select wire:model="product_id" name="product_id"
                                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Supplier -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Supplier</label>
                                <select wire:model="supplier_id" name="supplier_id"
                                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                                    <option value="">Pilih Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Type -->

                            <div>
                                <label class="text-sm text-heading block font-medium mb-2.5">
                                    Tipe Transaksi
                                </label>

                                <select name="type" wire:model="type" class="text-sm text-heading border border-default-medium
                       focus:border-brand focus:ring-brand
                       px-3 py-2.5 rounded-base shadow-xs w-full">
                                    <option value="">Pilih Tipe Transaksi</option>
                                    <option value="in">Stok Masuk</option>
                                    <option value="out">Stok Keluar</option>
                                </select>

                                @error('type')
                                    <span class="text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>


                            <!-- Quantity -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Kuantitas</label>
                                <input wire:model="quantity" type="number" min="1" name="quantity"
                                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"
                                    placeholder="Masukkan jumlah">
                                @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Transaction Date -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Tanggal Transaksi</label>
                                <input wire:model="transaction_date" type="date" name="transaction_date"
                                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs">
                                @error('transaction_date') <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Deskripsi (Opsional)</label>
                                <textarea wire:model="description" name="description" rows="3"
                                    class="w-full px-3 py-2.5 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs"
                                    placeholder="Catatan tambahan tentang transaksi ini..."></textarea>
                                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Buttons -->
                            <div class="flex justify-end space-x-2 pt-4 border-t">
                                <button type="button" wire:click="closeModal"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Batal
                                </button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                                    <span wire:loading.remove wire:target="save">Simpan</span>
                                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Loading...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>