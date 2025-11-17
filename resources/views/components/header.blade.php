@props([
//    'header' => 'This is header',
    'breadcrumb' => 'this/is/breadcrumb'
])

@php
    $breadcrumb = explode('/', $breadcrumb);
@endphp
<div>
    <nav class="flex mb-2.5" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
            <li class="inline-flex items-center">
                <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                     height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                </svg>
            </li>
            @foreach ($breadcrumb as $index => $item)
                <li class="inline-flex items-center">
                    <div class="flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 rtl:rotate-180 text-body" aria-hidden="true"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m9 5 7 7-7 7"/>
                        </svg>
                        <span class="inline-flex items-center text-sm font-medium text-body-subtle">
                            {{ ucfirst($item) }}
                        </span>
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
{{--    <h2 class="text-3xl font-bold tracking-tight text-heading md:text-4xl mb-4">{{ $header }}</h2>--}}
</div>
