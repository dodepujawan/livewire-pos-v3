<div
    wire:loading.flex
    {{ $attributes }}
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40 backdrop-blur-sm"
>

    <div class="w-80 rounded-xl bg-white p-6 shadow-xl">

        <div class="flex flex-col items-center">

            <svg
                class="h-10 w-10 animate-spin text-blue-600"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                />
            </svg>

            <div class="mt-4 text-lg font-semibold">
                Mohon Tunggu...
            </div>

            <div class="mt-1 text-sm text-gray-500">
                Sedang memproses data.
            </div>

        </div>

    </div>

</div>
