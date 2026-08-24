<div x-data="{ currentPath: window.location.pathname, init() { document.addEventListener('livewire:navigated', () => { this.currentPath = window.location.pathname; }); } }" x-init="init()">
    @foreach($menus as $menu)
        <div>

            @if($menu->children->isNotEmpty())

                <div wire:click="toggleMenu({{ $menu->id }})"
                    @class([
                        'flex items-center justify-between h-11 px-3 rounded-lg cursor-pointer transition-all duration-200 border-l-2',
                        'bg-amber-500/25 text-amber-300 font-semibold border-amber-400' => $this->isActive($menu) || $this->hasActiveChild($menu),
                        'border-transparent hover:bg-white/10' => !$this->isActive($menu) && !$this->hasActiveChild($menu),
                    ])
                >
                    <div class="flex items-center gap-3">
                        @if($menu->icon)
                            <i class="{{ $menu->icon }} text-lg"></i>
                        @endif
                        <span class="text-sm font-medium">
                            {{ $menu->title }}
                        </span>
                    </div>
                    <span class="text-xs transition-transform duration-200">
                        <i class="ti {{ in_array($menu->id, $openedMenus) ? 'ti-chevron-down' : 'ti-chevron-right' }}"></i>
                    </span>
                </div>

                @if(in_array($menu->id, $openedMenus))
                    <div class="mt-1 space-y-1">
                        @foreach($menu->children as $child)
                            <a
                                @if($child->systemRoute) href="{{ route($child->systemRoute->route_name) }}" @endif
                                wire:navigate
                                data-path="{{ parse_url(route($child->systemRoute->route_name), PHP_URL_PATH) }}"
                                x-bind:class="'ml-4 h-10 flex items-center rounded-lg px-3 transition-all duration-200 text-sm border-l-2 ' + ($el.dataset.path === window.location.pathname ? 'bg-amber-500/25 text-amber-300 font-semibold border-amber-400' : 'border-transparent hover:bg-white/10')"

                            >
                                {{ $child->title }}
                            </a>
                        @endforeach
                    </div>
                @endif

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
                        'flex items-center gap-3 h-11 px-3 rounded-lg transition-all duration-200 border-l-2',
                        'bg-amber-500/25 text-amber-300 font-semibold border-amber-400' => $this->isActive($menu),
                        'border-transparent hover:bg-white/10' => !$this->isActive($menu) && $menu->systemRoute,
                        'text-gray-500 border-transparent' => !$menu->systemRoute,
                    ])
                >
                    @if($menu->icon)
                        <i class="{{ $menu->icon }} text-lg"></i>
                    @endif

                    <span class="text-sm font-medium">
                        {{ $menu->title }}
                    </span>
                </a>

            @endif

        </div>
    @endforeach
</div>
