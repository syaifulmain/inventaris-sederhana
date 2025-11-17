<x-slot:breadcrumb>{{ $breadcrumb }}</x-slot>
<div class="max-w-2xl mx-auto">
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Error Message -->
    @error('update')
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ $message }}
    </div>
    @enderror

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Update Profile</h2>

        <form wire:submit="updateProfile" class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nama
                </label>
                <input
                    wire:model="name"
                    type="text"
                    id="name"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <input
                    wire:model="email"
                    type="email"
                    id="email"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password Baru (kosongkan jika tidak ingin mengubah)
                </label>
                <input
                    wire:model="password"
                    type="password"
                    id="password"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Konfirmasi Password
                </label>
                <input
                    wire:model="password_confirmation"
                    type="password"
                    id="password_confirmation"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>

            <!-- Submit Button -->
            <div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 disabled:opacity-50 flex justify-center"
                >
                    <span wire:loading.remove wire:target="updateProfile">Update Profile</span>
                    <span wire:loading wire:target="updateProfile">Loading...</span>
                </button>
            </div>
        </form>
    </div>
</div>
