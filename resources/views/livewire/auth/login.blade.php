<div class="flex justify-center items-center h-screen">
    <div class="w-full max-w-sm bg-neutral-primary-soft p-6 border border-default rounded-base shadow-xs">
        <form wire:submit="login">
            <h5 class="text-xl font-semibold text-heading mb-6">Masuk ke aplikasi</h5>
            @error('login')
                <div class="mb-4 text-sm bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-base">
                    {{ $message }}
                </div>
            @enderror
            <div class="mb-4">
                <label for="email" class="block mb-2.5 text-sm font-medium text-heading">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                             height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                  d="m3.5 5.5 7.893 6.036a1 1 0 0 0 1.214 0L20.5 5.5M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"/>
                        </svg>
                    </div>
                    <input wire:model="email" type="text" id="email"
                           class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 shadow-xs placeholder:text-body"
                           placeholder="nama@contoh.com">
                </div>
                @error('email')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-2.5 text-sm font-medium text-heading">Kata sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-body" aria-hidden="true"
                             xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                        </svg>

                    </div>
                    <input wire:model="password" type="password" id="password"
                           class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 shadow-xs placeholder:text-body"
                           placeholder="•••••••••">
                </div>
                @error('password')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-6">
                <div class="flex items-center">
                    <input
                        wire:model="remember"
                        type="checkbox"
                        id="remember"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Ingat saya
                    </label>
                </div>
            </div>
            <button type="submit"
                    class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none w-full mb-3">
                Masuk
            </button>
        </form>
    </div>
</div>
