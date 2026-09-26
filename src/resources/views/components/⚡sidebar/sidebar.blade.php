<div x-data="{ currentPath: window.location.pathname, init() { document.addEventListener('livewire:navigated', () => { this.currentPath = window.location.pathname; }); } }" x-init="init()">
    @foreach($menus as $menu)
        <div wire:key="sidebar-menu-{{ $menu->id }}">

            @if(filled($menu->sidebar_heading))
                <p class="px-3 mt-4 mb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-500">
                    {{ $menu->sidebar_heading }}
                </p>
            @endif

            @if($menu->children->isNotEmpty())
                <div
                    x-data="{ expanded: @js(in_array($menu->id, $openedMenus)) }"
                    x-on:sidebar-active-menus.window="if ($event.detail.openedMenus.includes({{ $menu->id }})) expanded = true"
                >
                    <button
                        type="button"
                        aria-controls="sidebar-submenu-{{ $menu->id }}"
                        :aria-expanded="expanded"
                        @click="expanded = !expanded"
                        @class([
                            'group flex w-full items-center justify-between h-11 px-3 rounded-lg border-l-2 text-left transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-400/60 motion-reduce:transition-none',
                            'bg-amber-500/25 text-amber-300 font-semibold border-amber-400 shadow-[inset_0_0_20px_rgba(251,191,36,0.06)]' => $this->isActive($menu) || $this->hasActiveChild($menu),
                            'border-transparent text-slate-200 hover:border-amber-400/70 hover:bg-amber-400/[0.06] hover:text-white hover:translate-x-0.5' => !$this->isActive($menu) && !$this->hasActiveChild($menu),
                        ])
                    >
                        <span class="flex min-w-0 items-center gap-3">
                            @if($menu->icon)
                                <i class="{{ $menu->icon }} text-lg transition-colors duration-200 group-hover:text-amber-300"></i>
                            @endif
                            <span class="truncate text-sm font-medium">
                                {{ $menu->title }}
                            </span>
                        </span>
                        <i
                            class="ti ti-chevron-right text-xs text-slate-400 transition duration-200 group-hover:text-amber-300 motion-reduce:transition-none"
                            :class="expanded ? 'rotate-90 text-amber-300' : ''"
                            aria-hidden="true"
                        ></i>
                    </button>

                    <div
                        id="sidebar-submenu-{{ $menu->id }}"
                        class="grid grid-rows-[0fr] -translate-y-1 opacity-0 transition-all duration-200 ease-out motion-reduce:transition-none"
                        :class="expanded ? 'grid-rows-[1fr] translate-y-0 opacity-100' : ''"
                        aria-hidden="true"
                        :aria-hidden="!expanded"
                        inert
                        :inert="!expanded"
                    >
                        <div class="min-h-0 overflow-hidden">
                            <div class="mt-1 space-y-1">
                                @foreach($menu->children as $child)
                                    @if($child->systemRoute)
                                        <a
                                            href="{{ route($child->systemRoute->route_name) }}"
                                            wire:navigate
                                            data-path="{{ parse_url(route($child->systemRoute->route_name), PHP_URL_PATH) }}"
                                            x-bind:class="'group ml-4 flex h-10 items-center rounded-lg border-l-2 px-3 text-sm transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-400/50 motion-reduce:transition-none ' + ($el.dataset.path === currentPath ? 'bg-amber-500/20 text-amber-300 font-semibold border-amber-400' : 'border-transparent text-slate-300 hover:translate-x-0.5 hover:border-amber-400/60 hover:bg-white/[0.05] hover:text-white')"
                                            :aria-current="$el.dataset.path === currentPath ? 'page' : null"
                                        >
                                            {{ $child->title }}
                                        </a>
                                    @else
                                        <span class="ml-4 flex h-10 items-center rounded-lg border-l-2 border-transparent px-3 text-sm text-slate-500">
                                            {{ $child->title }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else

                <a
                    @if($menu->systemRoute)
                        href="{{ route($menu->systemRoute->route_name) }}"
                        wire:navigate
                        wire:current="bg-amber-500/25 text-amber-300 font-semibold border-l-2 border-amber-400"
                    @else
                        href="javascript:void(0)"
                        style="pointer-events: none; cursor: default;"
                    @endif
                    @class([
                        'group flex items-center gap-3 h-11 px-3 rounded-lg border-l-2 transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-400/60 motion-reduce:transition-none',
                        'bg-amber-500/25 text-amber-300 font-semibold border-amber-400' => $this->isActive($menu),
                        'border-transparent text-slate-200 hover:border-amber-400/70 hover:bg-amber-400/[0.06] hover:text-white hover:translate-x-0.5' => !$this->isActive($menu) && $menu->systemRoute,
                        'text-gray-500 border-transparent' => !$menu->systemRoute,
                    ])
                >
                    @if($menu->icon)
                        <i class="{{ $menu->icon }} text-lg transition-colors duration-200 group-hover:text-amber-300"></i>
                    @endif

                    <span class="text-sm font-medium">
                        {{ $menu->title }}
                    </span>
                </a>

            @endif

        </div>
    @endforeach
</div>
