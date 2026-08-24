<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="route-name" content="{{ request()->route()?->getName() }}">
    <title>POPS Point Of Purchase System</title>
    <link rel="icon" href="{{ asset('gambar/pops_only.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-100">

<div class="relative">

    {{-- Navbar + Sidebar: satu persist & satu Alpine state --}}
    @persist('chrome')
    <div x-data="{ open: false }" @close-sidebar.window="open = false">
        @include('layouts.sidebar')
        @include('layouts.navbar')
    </div>
    @endpersist

    {{-- CONTENT --}}
    <main class="p-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

</div>

@livewireScriptConfig
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
    if (! window.__sidebarNavHook) {
        window.__sidebarNavHook = true;

        document.addEventListener('livewire:navigate', () => {
            window.dispatchEvent(new CustomEvent('close-sidebar'));
        });

        document.addEventListener('livewire:navigated', () => {
            Livewire.dispatch('refresh-sidebar');
        });
    }
</script>
</body>
</html>
