<div class="max-w-7xl mx-auto py-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Permission Matrix</h1>

        <a href="{{ route('auth.register.list') }}" wire:navigate
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
            Kembali ke User List
        </a>
    </div>

    <!-- INFO -->
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-700">
            <strong>Info:</strong> Centang kotak untuk memberikan permission ke role. Klik lagi untuk mencabut.
        </p>
    </div>

    <!-- MATRIX TABLE -->
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 min-w-[150px] sticky left-0 bg-gray-100 z-10">Role</th>
                        @foreach ($permissions as $permissionId => $permissionName)
                            <th class="px-3 py-3 min-w-[180px] text-center">
                                <div class="font-medium">{{ $permissionName }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse ($roles as $roleId => $roleName)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-800 sticky left-0 bg-white z-10 border-r">
                                {{ $roleName }}
                            </td>

                            @foreach ($permissions as $permissionId => $permissionName)
                                <td class="px-3 py-3 text-center">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            wire:click="togglePermission({{ $roleId }}, {{ $permissionId }})"
                                            {{ $this->hasPermission($roleId, $permissionId) ? 'checked' : '' }}
                                            class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                                        >
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($permissions) + 1 }}" class="text-center py-8 text-gray-500">
                                Data role atau permission belum ada. Jalankan <code>php artisan app:sync-auth</code> terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SUMMARY -->
    @if (count($roles) > 0 && count($permissions) > 0)
        <div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="font-medium">Total Role:</span> {{ count($roles) }}
                </div>
                <div>
                    <span class="font-medium">Total Permission:</span> {{ count($permissions) }}
                </div>
                <div class="col-span-2">
                    <span class="font-medium">Total Assignment:</span>
                    {{ array_sum(array_map('count', $rolePermissions)) }}
                </div>
            </div>
        </div>
    @endif

</div>
