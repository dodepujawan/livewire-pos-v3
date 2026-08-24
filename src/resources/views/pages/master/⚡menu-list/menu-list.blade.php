<div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">
            Menu Management
        </h1>
        <div class="flex gap-2">
            <a href="{{ route('master.launcher-group.list') }}" wire:navigate class="rounded-lg bg-gray-700 px-4 py-2 text-sm text-white hover:bg-gray-800">
                Launcher Groups
            </a>
            <a href="{{ route('master.menu.create') }}" wire:navigate class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                Tambah Menu
            </a>
        </div>
    </div>
    <div class="mb-6">
        <input wire:model.live.debounce.300ms="searchMenuKeyword" type="text" placeholder="Cari menu..." class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none">
    </div>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Title</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Parent</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Route</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold">Sort</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold">Sidebar</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold">Launcher Group</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($menuData as $menu)
                    <tr>
                        <td class="px-4 py-3">{{ $menu->title }}</td>
                        <td class="px-4 py-3">{{ $menu->parent?->title ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $menu->systemRoute?->route_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $menu->sort_order }}</td>
                        <td class="px-4 py-3 text-center">{{ $menu->is_sidebar ? 'Ya' : 'Tidak' }}</td>
                        <td class="px-4 py-3 text-center">{{ $menu->launcherGroup?->label ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('master.menu.edit', $menu) }}" wire:navigate class="rounded bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600">Edit</a>
                                <button type="button" wire:click="delete({{ $menu->id }})" wire:confirm="Yakin ingin menghapus menu ini?" class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Data menu belum tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
