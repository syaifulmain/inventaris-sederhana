<div>
    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar"
            type="button"
            class="text-heading bg-transparent box-border border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded-base ms-3 mt-3 text-sm p-2 focus:outline-none inline-flex sm:hidden">
        <span class="sr-only">Open sidebar</span>
        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
             viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10"/>
        </svg>
    </button>

    <aside id="logo-sidebar"
           class="fixed top-0 left-0 z-40 w-64 h-full transition-transform -translate-x-full sm:translate-x-0"
           aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto bg-neutral-primary-soft border-e border-default flex flex-col">
            <div class="overflow-y-auto">
                <div class="flex items-center ps-2.5 mb-5">
                    <img src="https://flowbite.com/docs/images/logo.svg" class="h-6 me-3" alt="Sistem Inventaris"/>
                    <span class="self-center text-lg text-heading font-semibold whitespace-nowrap">Sistem Inventaris</span>
                </div>
                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="#"
                           class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                 viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/>
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/>
                            </svg>
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('admin.users') }}"
                               class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                                <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                     viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                          d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3.05A2.5 2.5 0 1 1 9 5.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3.05a2.5 2.5 0 1 0-2-4.45m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/>
                                </svg>
                                <span class="ms-3">Manajemen Pengguna</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('categories') }}"
                           class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z"/>
                            </svg>
                            <span class="ms-3">Manajemen Kategori</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products') }}"
                           class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="m9 5 7 7-7 7"/>
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4h1.5L8 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm.75-3H7.5M11 7H6.312M17 4v6m-3-3h6"/>
                            </svg>
                            <span class="ms-3">Manajemen Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('suppliers') }}"
                           class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/>
                            </svg>
                            <span class="ms-3">Manajemen Supplier</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('stocks') }}"
                           class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="m16 10 3-3m0 0-3-3m3 3H5v3m3 4-3 3m0 0 3 3m-3-3h14v-3"/>
                            </svg>
                            <span class="ms-3">Manajemen Stok</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="mt-auto pt-4">
                <hr class="h-px my-4 bg-neutral-quaternary border-0">
                <div class="flex items-center ms-3">
                    <div>
                        <button type="button"
                                class="flex text-sm bg-transparent"
                                aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <a class="flex text-heading whitespace-nowrap">
                                <img class="w-10 h-10 rounded-full"
                                     src="https://flowbite.com/docs/images/people/profile-picture-5.jpg"
                                     alt="Jese image">
                                <div class="ps-3">
                                    <div class="text-base font-semibold text-left">{{ auth()->user()->name }}</div>
                                    <div class="font-normal text-body">{{ auth()->user()->email }}</div>
                                </div>
                            </a>
                        </button>
                    </div>
                    <div
                        class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44"
                        id="dropdown-user">
                        <ul class="p-2 text-sm text-body font-medium" role="none">
                            <li>
                                <a href="{{ route('profile') }}"
                                   class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                   role="menuitem">Profil</a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center w-full p-2 hover:bg-red-500 hover:text-white rounded text-left"
                                            role="menuitem">Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>
