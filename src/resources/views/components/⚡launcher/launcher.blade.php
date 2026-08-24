@php
    $palette = [
        ['accent' => '#D4AF37', 'accentSoft' => 'rgba(212,175,55,0.42)', 'ring' => 'rgba(212,175,55,0.20)', 'border' => 'rgba(212,175,55,0.30)'], // Gold
        ['accent' => '#5B8DB8', 'accentSoft' => 'rgba(91,141,184,0.42)', 'ring' => 'rgba(91,141,184,0.20)', 'border' => 'rgba(91,141,184,0.30)'], // Steel Blue
        ['accent' => '#5FA88D', 'accentSoft' => 'rgba(95,168,141,0.42)', 'ring' => 'rgba(95,168,141,0.20)', 'border' => 'rgba(95,168,141,0.30)'], // Jade
        ['accent' => '#B56B7A', 'accentSoft' => 'rgba(181,107,122,0.42)', 'ring' => 'rgba(181,107,122,0.20)', 'border' => 'rgba(181,107,122,0.30)'], // Burgundy Rose
        ['accent' => '#8B78B5', 'accentSoft' => 'rgba(139,120,181,0.42)', 'ring' => 'rgba(139,120,181,0.20)', 'border' => 'rgba(139,120,181,0.30)'], // Amethyst
        ['accent' => '#C47A45', 'accentSoft' => 'rgba(196,122,69,0.42)', 'ring' => 'rgba(196,122,69,0.20)', 'border' => 'rgba(196,122,69,0.30)'], // Copper
        ['accent' => '#6E9FA3', 'accentSoft' => 'rgba(110,159,163,0.42)', 'ring' => 'rgba(110,159,163,0.20)', 'border' => 'rgba(110,159,163,0.30)'], // Teal Steel
        ['accent' => '#8C6F52', 'accentSoft' => 'rgba(140,111,82,0.42)', 'ring' => 'rgba(140,111,82,0.20)', 'border' => 'rgba(140,111,82,0.30)'], // Bronze
        ['accent' => '#A65D5D', 'accentSoft' => 'rgba(166,93,93,0.42)', 'ring' => 'rgba(166,93,93,0.20)', 'border' => 'rgba(166,93,93,0.30)'], // Ruby
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
    @foreach($groupedMenus as $group => $menus)
        @php
            $groupModel = \App\Models\LauncherGroup::where('key', $group)->first();
            $color = $palette[$loop->index % count($palette)];
        @endphp

        {{-- Untuk Border Color --}}
        {{-- <div
            class="h-full flex flex-col rounded-xl bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_20px_-12px_rgba(15,23,42,0.12)]"
            style="border: 3px solid {{ $color['border'] }};"
        > --}}
            <div
                class="h-full flex flex-col rounded-xl bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_20px_-12px_rgba(15,23,42,0.12)]" style=" border: 1px solid rgba(148,163,184,0.55); box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 1px 2px rgba(15,23,42,0.04), 0 8px 20px -12px rgba(15,23,42,0.12);"
            >
            {{-- Group Header --}}
            <div class="flex items-center gap-2.5 mb-3.5 shrink-0">
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#0F1B33] to-[#1E2E52] shadow-[0_2px_5px_rgba(15,27,51,0.3)]"
                    style="color: {{ $color['accent'] }}; box-shadow: 0 2px 5px rgba(15,27,51,0.3), 0 0 0 1px {{ $color['ring'] }} inset;"
                >
                    @if($groupModel && $groupModel->icon)
                        <i class="{{ $groupModel->icon }} text-xs"></i>
                    @else
                        <i class="fa-solid fa-layer-group text-xs"></i>
                    @endif
                </span>
                <div class="min-w-0">
                    <h2 class="font-['Plus_Jakarta_Sans'] text-[13px] font-semibold tracking-tight text-slate-800 leading-tight">
                        {{ $groupModel?->label ?? ucfirst(str_replace('_', ' ', $group)) }}
                    </h2>
                    <p class="font-mono text-[8px] uppercase tracking-[0.2em] text-slate-400">
                        {{ $menus->count() }} Modul
                    </p>
                </div>
                {{-- <div class="ml-auto h-px flex-1 bg-gradient-to-r from-slate-200 via-slate-200 to-transparent"></div> --}}
                <div class="ml-auto h-px flex-1" style="background: linear-gradient(to right, {{ $color['border'] }}, {{ $color['border'] }} 40%, transparent);"></div>
            </div>

            <div class="flex-1 flex flex-col justify-center">
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    @foreach($menus as $menu)
                        <a
                            href="{{ $menu->systemRoute?->route_name ? route($menu->systemRoute->route_name) : '#' }}"
                            wire:navigate
                            class="group relative flex flex-col items-center justify-center gap-1.5 rounded-lg border border-slate-200/80 bg-white px-2 py-3.5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-all duration-300 hover:-translate-y-0.5"
                            style="--tile-accent: {{ $color['accent'] }}; --tile-accent-soft: {{ $color['accentSoft'] }};"
                            onmouseover="this.style.borderColor='var(--tile-accent)'; this.style.boxShadow='0 10px 20px -10px var(--tile-accent-soft)';"
                            onmouseout="this.style.borderColor=''; this.style.boxShadow='0 1px 2px rgba(15,23,42,0.04)';"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-md bg-gradient-to-br from-[#0F1B33] to-[#1E2E52] transition-all duration-300"
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
                            <span class="text-[10px] font-medium text-center leading-tight text-slate-600 line-clamp-2 group-hover:text-slate-900">
                                {{ $menu->title }}
                            </span>

                            <span class="pointer-events-none absolute bottom-0 left-1/2 h-[2px] w-0 -translate-x-1/2 transition-all duration-300 group-hover:w-6" style="background: {{ $color['accent'] }};"></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    @if($groupedMenus->isEmpty())
        <div class="col-span-full text-center text-slate-400 py-16">
            <p class="font-mono text-xs uppercase tracking-widest">Tidak ada menu untuk Launcher</p>
        </div>
    @endif
</div>
