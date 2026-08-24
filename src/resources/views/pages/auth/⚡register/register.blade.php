<div class="max-w-xl mx-auto">

    <h1 class="text-xl font-bold mb-4">Register User</h1>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-700 p-2 mb-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-4 shadow rounded space-y-3">

        <!-- Nama -->
        <div>
            <input type="text" wire:model="regName"
                placeholder="Nama"
                class="w-full border p-2 rounded">
            @error('regName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Email -->
        <div>
            <input type="email" wire:model="regEmail"
                placeholder="Email"
                class="w-full border p-2 rounded">
            @error('regEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div>
            <input type="password" wire:model="regPassword"
                placeholder="Password"
                class="w-full border p-2 rounded">
            @error('regPassword') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Role -->
        <div>
            <select wire:model="regRole"
                class="w-full border p-2 rounded">
                <option value="">-- pilih role --</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                @endforeach
            </select>
            @error('regRole') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Button -->
        <button wire:click="register"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Simpan
        </button>

    </div>
</div>