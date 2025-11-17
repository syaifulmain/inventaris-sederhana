<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Page Title' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.0/dist/flowbite.min.js"></script>
{{ $slot }}
</body>
</html>
