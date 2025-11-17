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
                    <img src="https://flowbite.com/docs/images/logo.svg" class="h-6 me-3" alt="Flowbite Logo"/>
                    <span class="self-center text-lg text-heading font-semibold whitespace-nowrap">Flowbite</span>
                </div>
                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="#"
                           class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                            <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/>
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/>
                            </svg>
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                        <li>
                            <a href="#"
                               class="flex items-center px-2 py-1.5 text-body rounded-base hover:bg-neutral-tertiary hover:text-fg-brand group">
                                <svg class="w-5 h-5 transition duration-75 group-hover:text-fg-brand" aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/>
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/>
                                </svg>
                                <span class="ms-3">Manajemen Pengguna</span>
                            </a>
                        </li>
                    @endif
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
