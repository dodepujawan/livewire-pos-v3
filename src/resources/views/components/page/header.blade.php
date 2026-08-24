@props([
    'title',
    'description' => null,
])

<div class="mb-6 flex items-center justify-between">

    <div>

        <h1 class="text-2xl font-bold text-gray-800">

            {{ $title }}

        </h1>

        @if($description)

            <p class="mt-1 text-sm text-gray-500">

                {{ $description }}

            </p>

        @endif

    </div>

    <div>

        {{ $actions ?? '' }}

    </div>

</div>
