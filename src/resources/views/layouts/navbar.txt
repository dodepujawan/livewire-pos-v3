@php
    $routeName = request()->route()?->getName() ?? '';
    $segments = $routeName ? explode('.', $routeName) : [];
    $labels = [
        'master'    => 'Master',
        'transaksi' => 'Transaksi',
        'auth'      => 'Auth',
        'system'    => 'System',
        'barang'    => 'Barang',
        'menu'      => 'Menu',
        'penjualan' => 'Penjualan',
        'register'  => 'User',
        'role'      => 'Role',
        'list'      => 'Daftar',
        'create'    => 'Tambah',
        'edit'      => 'Edit',
        'show'      => 'Detail',
        'dashboard' => 'Dashboard',
    ];
    $pageTitle = $labels[$segments[0] ?? ''] ?? ($segments ? ucfirst(str_replace('-', ' ', $segments[0])) : 'Dashboard');
@endphp

<div class="p-4 md:p-6">

    <nav class="relative overflow-visible rounded-xl border border-white/10 px-4 py-3 flex items-center justify-between flex-wrap gap-3
                bg-gradient-to-r from-[#0f172a] via-[#1a2a6c] to-[#0f172a] shadow-lg shadow-black/20">

        {{-- subtle glow --}}
        <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-400/10 blur-2xl"></div>

        {{-- LEFT: Toggle + Brand + Breadcrumb --}}
        <div class="relative flex items-center gap-3 min-w-0 flex-1">

            <button
                type="button"
                @click="open = true"
                class="shrink-0 p-2 rounded-lg text-slate-400 hover:text-amber-300 hover:bg-white/10 transition"
                aria-label="Buka menu"
            >
                <i class="ti ti-menu-2 text-xl"></i>
            </button>

            <div class="hidden sm:flex items-center gap-2.5 shrink-0">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg
                            bg-gradient-to-br from-amber-400/20 to-transparent
                            border border-amber-400/30">
                    <img src="{{ asset('gambar/pops_only.jpeg') }}" alt="POPS" class="h-6 w-6 object-contain">
                </div>
                <div class="leading-tight">
                    <div class="text-xs font-bold text-white tracking-wide">POPS</div>
                    <div class="text-[10px] text-amber-200/70">Cashier &amp; Ledger</div>
                </div>
            </div>

            <div class="hidden md:block h-6 w-px bg-white/10 shrink-0"></div>

            {{-- Breadcrumb / page title --}}
            <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-400 min-w-0">
                <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-amber-300 transition shrink-0">
                    <i class="ti ti-home"></i>
                </a>
                @foreach($segments as $segment)
                    <span class="text-slate-600 shrink-0">/</span>
                    @if($loop->last)
                        <span class="text-amber-300 font-medium truncate">{{ $labels[$segment] ?? ucfirst(str_replace('-', ' ', $segment)) }}</span>
                    @else
                        <span class="truncate">{{ $labels[$segment] ?? ucfirst(str_replace('-', ' ', $segment)) }}</span>
                    @endif
                @endforeach
            </div>

            {{-- Mobile: page title only --}}
            <div class="md:hidden min-w-0">
                <div class="text-sm font-semibold text-white truncate">{{ $pageTitle }}</div>
            </div>
        </div>

        {{-- RIGHT: Kantor + Profile --}}
        <div class="relative flex items-center gap-2 sm:gap-3 shrink-0 text-sm">

            <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-300 bg-white/5 px-3 py-1.5 rounded-lg border border-white/10">
                <i class="ti ti-map-pin text-amber-400"></i>
                <span class="font-medium">Kantor Utama</span>
            </div>

            <div class="relative z-50" x-data="{ profileOpen: false }">
                <button
                    type="button"
                    @click.stop="profileOpen = !profileOpen"
                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-white/10
                           hover:border-amber-400/30 hover:bg-white/5 transition
                           focus:outline-none focus:ring-2 focus:ring-amber-400/40"
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-md
                                 bg-gradient-to-br from-amber-400/30 to-amber-600/10
                                 border border-amber-400/25 text-amber-300 text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                    </span>
                    <span class="hidden sm:block font-medium text-white max-w-[120px] truncate">
                        {{ auth()->user()->name ?? 'Guest' }}
                    </span>
                    <i class="ti ti-chevron-down text-slate-400 text-sm transition-transform duration-200"
                       :class="profileOpen && 'rotate-180'"></i>
                </button>

                <div
                    x-show="profileOpen"
                    x-cloak
                    @click.away="profileOpen = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="absolute right-0 top-full mt-2 w-52 rounded-xl border border-white/10 bg-[#0f172a] shadow-xl py-1 z-[100]"
                >
                    <div class="px-4 py-2.5 border-b border-white/10">
                        <div class="text-sm font-semibold text-white truncate">
                            {{ auth()->user()->name ?? 'Guest' }}
                        </div>
                        <div class="text-xs text-slate-400 truncate">
                            {{ auth()->user()->email ?? '' }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2 text-left px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition">
                            <i class="ti ti-logout"></i>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</div>
