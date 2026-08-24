<x-form.card>
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-lg font-semibold">Edit Menu</span>
                <span class="text-sm text-gray-500">Edit an existing navigation/menu item for your application</span>
            </div>
        </div>
    </x-slot:title>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="md:col-span-2 bg-white p-4 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Parent Menu --}}
                    <div class="sm:col-span-2">
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <x-form.select
                                    label="Parent Menu"
                                    name="parent_id"
                                    wire:model="parent_id"
                                >
                                    <option value="">Edit Root Menu (No Parent)</option>
                                    @foreach($parentMenus as $menu)
                                        <option value="{{ $menu->id }}">{{ $menu->title }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 -mt-3">
                            Pilih <strong>Edit Root Menu</strong> untuk membuat menu utama,
                            atau pilih salah satu parent untuk membuat submenu.
                        </p>
                    </div>

                    {{-- Route --}}
                    <div class="sm:col-span-2">
                        <x-form.tom-select
                            label="Route"
                            name="system_route_id"
                            wire:model.live="system_route_id"
                            data-tom-select
                            data-placeholder="No Route"
                        >
                            <option value=""></option>
                            @foreach($systemRoutes as $route)
                                <option value="{{ $route->id }}">
                                    {{ $route->display_name }}
                                    ({{ $route->route_name }})
                                </option>
                            @endforeach
                        </x-form.tom-select>
                        <p class="text-xs text-gray-500 -mt-3">
                            Kosongkan jika menu hanya digunakan sebagai Parent Menu.
                        </p>
                    </div>

                    {{-- Title --}}
                    <x-form.input
                        label="Title"
                        name="title"
                        wire:model.live="title"
                    />

                    {{-- Icon --}}
                    <x-form.input
                        label="Icon"
                        name="icon"
                        wire:model.live="icon"
                    />

                    {{-- Sort --}}
                    <x-form.input
                        label="Sort Order"
                        name="sort_order"
                        type="number"
                        wire:model="sort_order"
                    />

                    {{-- Sidebar --}}
                    <div class="flex items-center gap-2 pt-7">
                        <input
                            id="is_sidebar"
                            type="checkbox"
                            wire:model="is_sidebar"
                            class="rounded border-gray-300"
                        >
                        <label for="is_sidebar">Show in Sidebar</label>
                    </div>

                    {{-- Launcher Group --}}
                    <div class="flex items-center gap-2 pt-7">
                        <x-form.select
                            label="Launcher Group"
                            name="launcher_group"
                            wire:model="launcher_group"
                        >
                            <option value="">Tidak tampil di Launcher</option>
                            @foreach($launcherGroups as $group)
                                <option value="{{ $group->key }}">{{ $group->label }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>
            </div>

            {{-- Preview --}}
            <div class="md:col-span-1">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Preview</h3>
                    <div class="border rounded-lg p-4 bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 text-xl">
                                @if($icon)
                                    <i class="{{ $icon }}"></i>
                                @else
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="font-semibold text-lg text-gray-800">{{ $title ?: 'Menu Title' }}</div>
                                <div class="text-sm text-gray-500">
                                    Parent : {{ optional($parentMenus->firstWhere('id', $parent_id))->title ?? 'Create Root Menu' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 border-t pt-3 text-sm text-gray-500">
                            Route : {{ optional($systemRoutes->firstWhere('id', $system_route_id))->route_name ?? 'No Route' }}
                        </div>
                        <div class="mt-2 text-sm text-gray-500">Sidebar : {{ $is_sidebar ? 'Yes' : 'No' }}</div>
                        <div class="mt-1 text-sm text-gray-500">Launcher Group : {{ $launcher_group ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('master.menu.list') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="button" wire:click="update" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                Update
            </button>
        </div>
    </div>
</x-form.card>
