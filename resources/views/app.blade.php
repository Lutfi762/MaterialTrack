<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'MaterialTrack') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Apply saved/system theme before paint to avoid flash of wrong theme.
        (function () {
            var saved = localStorage.getItem('darkMode');
            var dark = saved === '1' || (saved === null && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="bg-gray-50">
    @inertia
</body>

</html>