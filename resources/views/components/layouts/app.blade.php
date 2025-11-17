<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Page Title' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Force Light Theme untuk semua elemen pagination */
        .dark nav * {
            background-color: white !important;
            color: #374151 !important;
            border-color: #d1d5db !important;
        }

        /* Halaman aktif */
        .dark nav span[aria-current="page"],
        .dark nav span[aria-current="page"] > span {
            background-color: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
        }

        /* Link hover */
        .dark nav a:hover {
            background-color: #f3f4f6 !important;
        }

        /* Non-dark mode (normal) */
        nav[role="navigation"] {
            color-scheme: light !important;
        }

        nav[role="navigation"] * {
            background-color: white !important;
            color: #374151 !important;
        }

        nav[role="navigation"] a {
            border: 1px solid #d1d5db !important;
        }

        nav[role="navigation"] span[aria-current="page"],
        nav[role="navigation"] span[aria-current="page"] span {
            background-color: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
        }

        nav[role="navigation"] a:hover {
            background-color: #f3f4f6 !important;
        }
    </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.0/dist/flowbite.min.js"></script>
<x-sidebar/>
<div class="p-4 sm:ml-64">
    <x-header :breadcrumb="$breadcrumb"/>
    <div class="p-4 border-1 border-default border-dashed rounded-base">
        {{ $slot }}
    </div>
    <x-footer/>
</div>
</body>
</html>
