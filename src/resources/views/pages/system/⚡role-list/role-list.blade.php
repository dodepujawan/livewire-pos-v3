<div>

    <x-page.header
        title="Role Management"
        description="Kelola role, permission, dan pengguna yang menggunakannya."
    >
        {{-- <x-slot:actions>
            <a
                href="{{ route('system.role.create') }}"
                wire:navigate
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Tambah Role
            </a>
        </x-slot:actions> --}}
    </x-page.header>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <x-form.card>
        <x-form.input
            label="Cari Role"
            name="searchKeyword"
            placeholder="Masukkan nama role..."
            wire:model.live.debounce.300ms="searchKeyword"
        />
    </x-form.card>

    <div class="mt-6">
        <x-table.table>
            <thead>
                <tr>
                    <x-table.th>Nama Role</x-table.th>
                    <x-table.th class="text-center">Jumlah Permission</x-table.th>
                    <x-table.th class="text-center">Jumlah User</x-table.th>
                    <x-table.th class="text-center">Aksi</x-table.th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse ($roles as $role)
                    <tr wire:key="role-{{ $role->id }}" class="hover:bg-gray-50">
                        <x-table.td class="font-medium text-gray-800">
                            {{ $role->name }}
                        </x-table.td>

                        <x-table.td class="text-center">
                            {{ $role->permissions_count }}
                        </x-table.td>

                        <x-table.td class="text-center">
                            {{ $role->users_count }}
                        </x-table.td>

                        <x-table.td class="text-center">
                            <div class="flex justify-center gap-2">
                                {{-- <a
                                    href="{{ route('system.role.edit', $role) }}"
                                    wire:navigate
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                >
                                    Edit
                                </a> --}}

                                <x-button.danger
                                    wire:click="delete({{ $role->id }})"
                                    wire:confirm="Yakin ingin menghapus role {{ $role->name }}?"
                                >
                                    Hapus
                                </x-button.danger>
                            </div>
                        </x-table.td>
                    </tr>
                @empty
                    <x-table.empty colspan="4">
                        Data role belum tersedia.
                    </x-table.empty>
                @endforelse
            </tbody>
        </x-table.table>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </div>

</div>
