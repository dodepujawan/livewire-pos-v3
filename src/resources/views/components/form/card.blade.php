<div class="rounded-lg bg-white shadow border border-gray-200">

    @isset($title)
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">
                {{ $title }}
            </h2>
        </div>
    @endisset

    <div class="p-6">
        {{ $slot }}
    </div>

</div>
