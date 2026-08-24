<div class="max-w-xl mx-auto py-6">

    <h1 class="text-xl font-bold mb-4">Edit User</h1>

    <!-- FLASH -->
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4 space-y-4">
        <!-- NAME -->
        <div>
            <label class="text-sm text-gray-600">Nama</label>
            <input type="text" wire:model="editName" class="w-full border rounded px-3 py-2 mt-1">
        </div>

        <!-- EMAIL -->
        <div>
            <label class="text-sm text-gray-600">Email</label>
            <input type="email" wire:model="editEmail" class="w-full border rounded px-3 py-2 mt-1">
        </div>

        <!-- ROLE -->
        <div>
            <label class="text-sm text-gray-600">Role</label>
            <select wire:model="editRole"
                    class="w-full border rounded px-3 py-2 mt-1">
                <option value="">-- pilih role --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>

        <!-- PASSWORD -->
        <div>
            <label class="text-sm text-gray-600">Password</label>

            <input type="password"
                wire:model="editPassword"
                placeholder="Kosongkan jika tidak ingin mengubah password"
                class="w-full border rounded px-3 py-2 mt-1">

            @error('editPassword')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror

            <p class="text-xs text-gray-400 mt-1">
                Isi hanya jika ingin mengganti password
            </p>
        </div>

        <!-- ACTION -->
        <div class="flex justify-between pt-2 mt-2">
            <a href="{{ route('auth.register.list') }}" wire:navigate class="text-gray-600 hover:underline">
                ← Kembali
            </a>

            <button wire:click="update" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Update
            </button>
        </div>

    </div>

</div>