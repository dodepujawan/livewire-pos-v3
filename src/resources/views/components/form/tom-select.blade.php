@props([
    'label' => null,
    'name',
])

<div class="mb-5" wire:ignore>
    @if($label)
        <label
            for="{{ $name }}"
            class="mb-2 block text-sm font-medium text-gray-700"
        >
            {{ $label }}
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge([
            'class' =>
                'w-full rounded-lg border border-gray-300 px-3 py-2
                focus:border-blue-500 focus:ring focus:ring-blue-200'
        ]) }}
    >
        {{ $slot }}
    </select>

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>

<style>
    .ts-wrapper {
        width: 100%;
    }

    .ts-wrapper .ts-control {
        padding: 0.5rem 0.75rem !important;
        min-height: 28px !important;
        height: auto !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        line-height: 1.5 !important;
        background-color: #ffffff !important;
        font-size: 1rem !important;
    }

    .ts-wrapper .ts-control input {
        padding: 0.125rem 0 !important;
        height: auto !important;
        line-height: 1.5 !important;
    }

    .ts-wrapper.focus .ts-control {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    .ts-dropdown {
        margin-top: 0.25rem;
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }

    .ts-dropdown .ts-dropdown-content {
        padding: 0.25rem 0 !important;
    }

    .ts-dropdown-content .ts-option {
        padding: 0.5rem 0.75rem !important;
    }
</style>

@once
<script>
const initializeTomSelect = (root = document) => {
    const selects = root.matches?.('select[data-tom-select]')
        ? [root]
        : root.querySelectorAll('select[data-tom-select]');

    selects.forEach(select => {
        if (select.tomselect) {
            return;
        }

        new TomSelect(select, {
            create: false,
            allowEmptyOption: true,
            placeholder: select.dataset.placeholder || 'Select...',
            searchField: ['text'],
            maxOptions: 500,
        });
    });
};

document.addEventListener('livewire:init', () => {
    initializeTomSelect();
});

document.addEventListener('livewire:navigated', () => {
    initializeTomSelect();
});

Livewire.hook('morph.updated', ({ el }) => {
    initializeTomSelect(el);
});

Livewire.hook('morph.added', ({ el }) => {
    initializeTomSelect(el);
});
</script>
@endonce
