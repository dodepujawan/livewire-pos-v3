<x-form.card>
    <x-slot:title>
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-lg font-semibold">Launcher Group Manager</span>
                <span class="text-sm text-gray-500">Manage launcher categories for the dashboard</span>
            </div>
        </div>
    </x-slot:title>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 rounded-lg bg-red-100 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-lg bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="md:col-span-1 bg-white p-4 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 gap-4">
                    @if(!$editingId)
                    <x-form.input
                        label="Key"
                        name="key"
                        wire:model="key"
                    />
                    @error('key')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 -mt-2">Unique identifier, lowercase underscore.</p>
                    @endif

                    <x-form.input
                        label="Label"
                        name="label"
                        wire:model="label"
                    />
                    @error('label')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <x-form.input
                        label="Icon (Tabler)"
                        name="icon"
                        wire:model="icon"
                    />
                    @error('icon')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 -mt-2">Example: ti ti-receipt</p>

                    <x-form.input
                        label="Sort Order"
                        name="sortOrder"
                        type="number"
                        wire:model="sortOrder"
                    />
                    @error('sortOrder')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            type="checkbox"
                            wire:model="isActive"
                            class="rounded border-gray-300"
                        >
                        <label for="is_active">Active</label>
                    </div>
                    @error('isActive')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <div class="flex gap-2">
                        <button type="button" wire:click="save" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="cancelEdit" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- List --}}
            <div class="md:col-span-2">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Key</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Label</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Icon</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Sort</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Active</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Menus</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($groups as $group)
                                <tr>
                                    <td class="px-4 py-3">{{ $group->key }}</td>
                                    <td class="px-4 py-3">{{ $group->label }}</td>
                                    <td class="px-4 py-3">{{ $group->icon ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->sort_order }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->is_active ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3 text-center">{{ $group->menus_count }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <button type="button" wire:click="edit({{ $group->id }})" class="rounded bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600">Edit</button>
                                        <button type="button" wire:click="delete({{ $group->id }})" wire:confirm="Yakin ingin menghapus group ini?" @if($group->menus_count > 0) disabled @endif class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 @if($group->menus_count > 0) opacity-50 cursor-not-allowed @endif">{{ $group->menus_count > 0 ? 'Used' : 'Delete' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada launcher group.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-form.card>
