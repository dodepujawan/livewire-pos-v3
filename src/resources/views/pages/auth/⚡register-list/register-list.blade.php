<div class="max-w-6xl mx-auto py-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">User List</h1>

        <a href="{{ route('auth.register.create') }}" wire:navigate
           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm shadow">
            + Add User
        </a>
    </div>

    <!-- FLASH MESSAGE -->
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- TABLE -->
    <div class="bg-white shadow rounded-xl overflow-hidden">

        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($regUsers as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $user->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                {{ $user->getRoleNames()->first() ?? '-' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center space-x-2">

                            <!-- EDIT -->
                            <a href="{{ route('auth.register.edit', $user->id) }}"
                               wire:navigate
                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                Edit
                            </a>

                            <!-- DELETE -->
                            <button
                                wire:click="delete({{ $user->id }})"
                                onclick="confirm('Yakin hapus?') || event.stopImmediatePropagation()"
                                class="inline-block bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                Delete
                            </button>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-500">
                            Data user belum ada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $regUsers->links() }}
    </div>

</div>