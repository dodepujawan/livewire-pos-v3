@php
    $palette = [
        ['accent' => '#D39B18', 'wash' => 'rgba(255,248,225,0.82)', 'accentSoft' => 'rgba(211,155,24,0.22)', 'border' => 'rgba(211,155,24,0.30)'],
        ['accent' => '#2878C8', 'wash' => 'rgba(235,245,255,0.86)', 'accentSoft' => 'rgba(40,120,200,0.20)', 'border' => 'rgba(40,120,200,0.26)'],
        ['accent' => '#149B78', 'wash' => 'rgba(231,250,244,0.86)', 'accentSoft' => 'rgba(20,155,120,0.20)', 'border' => 'rgba(20,155,120,0.26)'],
        ['accent' => '#D45670', 'wash' => 'rgba(255,239,243,0.86)', 'accentSoft' => 'rgba(212,86,112,0.20)', 'border' => 'rgba(212,86,112,0.26)'],
        ['accent' => '#8058C7', 'wash' => 'rgba(245,239,255,0.88)', 'accentSoft' => 'rgba(128,88,199,0.20)', 'border' => 'rgba(128,88,199,0.26)'],
        ['accent' => '#D87327', 'wash' => 'rgba(255,243,233,0.88)', 'accentSoft' => 'rgba(216,115,39,0.20)', 'border' => 'rgba(216,115,39,0.26)'],
        ['accent' => '#148F9D', 'wash' => 'rgba(231,249,251,0.88)', 'accentSoft' => 'rgba(20,143,157,0.20)', 'border' => 'rgba(20,143,157,0.26)'],
    ];
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5">
    @foreach($groupedMenus as $group => $menus)
        @php
            $groupModel = $groupModels->get($group);
            $color = $palette[$loop->index % count($palette)];
        @endphp

        <section
            class="group/section relative flex h-full min-h-[212px] flex-col overflow-hidden rounded-2xl border bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.03),0_14px_30px_-20px_rgba(15,23,42,0.24)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_34px_-22px_var(--group-shadow)] sm:p-5"
            style="--group-accent: {{ $color['accent'] }}; --group-wash: {{ $color['wash'] }}; --group-shadow: {{ $color['accentSoft'] }}; border-color: {{ $color['border'] }};"
        >
            <div class="pointer-events-none absolute inset-x-0 top-0 h-24 opacity-80" style="background: linear-gradient(115deg, var(--group-wash), transparent 68%);"></div>
            <div class="pointer-events-none absolute -right-10 -top-10 h-24 w-24 rounded-full opacity-50 blur-2xl" style="background: var(--group-accent);"></div>

            {{-- Group Header --}}
            <div class="relative mb-4 flex shrink-0 items-center gap-3">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-lg shadow-slate-900/15"
                    style="background: linear-gradient(145deg, #17284a, #0d1830); color: {{ $color['accent'] }}; box-shadow: 0 6px 12px -7px {{ $color['accent'] }};"
                >
                    @if($groupModel && $groupModel->icon)
                        <i class="{{ $groupModel->icon }} text-sm"></i>
                    @else
                        <i class="fa-solid fa-layer-group text-sm"></i>
                    @endif
                </span>
                <div class="min-w-0">
                    <h2 class="font-['Plus_Jakarta_Sans'] text-sm font-bold tracking-tight text-slate-900 leading-tight">
                        {{ $groupModel?->label ?? ucfirst(str_replace('_', ' ', $group)) }}
                    </h2>
                    <p class="mt-1 font-mono text-[9px] uppercase tracking-[0.18em] text-slate-400">
                        {{ str_pad($menus->count(), 2, '0', STR_PAD_LEFT) }} Modul Tersedia
                    </p>
                </div>
                <div class="ml-auto flex h-7 w-7 items-center justify-center rounded-full border text-[10px] font-bold" style="color: var(--group-accent); border-color: {{ $color['border'] }}; background: var(--group-wash);">{{ $loop->iteration }}</div>
            </div>

            <div class="relative flex flex-1 flex-col justify-center">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($menus as $menu)
                        <a
                            href="{{ $menu->systemRoute?->route_name ? route($menu->systemRoute->route_name) : '#' }}"
                            wire:navigate
                            class="group relative flex min-h-[92px] flex-col items-center justify-center gap-2 rounded-xl border border-slate-200/80 bg-white/90 px-2 py-3 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-1 hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="--tile-accent: {{ $color['accent'] }}; --tile-accent-soft: {{ $color['accentSoft'] }};"
                            onmouseover="this.style.borderColor='var(--tile-accent)'; this.style.boxShadow='0 12px 22px -12px var(--tile-accent-soft)';"
                            onmouseout="this.style.borderColor=''; this.style.boxShadow='0 1px 2px rgba(15,23,42,0.04)';"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#122342] shadow-sm transition-all duration-300 group-hover:scale-105"
                                style="color: {{ $color['accent'] }};"
                            >
                                @if($menu->icon)
                                    <i class="{{ $menu->icon }} text-[12px]"></i>
                                @else
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                @endif
                            </div>
                            <span class="text-[11px] font-semibold text-center leading-snug text-slate-600 line-clamp-2 group-hover:text-slate-950">
                                {{ $menu->title }}
                            </span>

                            <span class="pointer-events-none absolute bottom-0 left-1/2 h-0.5 w-0 -translate-x-1/2 rounded-full transition-all duration-300 group-hover:w-8" style="background: {{ $color['accent'] }};"></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    @if($groupedMenus->isEmpty())
        <div class="col-span-full text-center text-slate-400 py-16">
            <p class="font-mono text-xs uppercase tracking-widest">Tidak ada menu untuk Launcher</p>
        </div>
    @endif
</div>
