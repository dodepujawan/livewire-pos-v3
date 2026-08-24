@extends('layouts.app')

@section('content')
    <div class="relative">
        <div class="mb-3 flex items-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-[#D4AF37]"></span>
            <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-slate-400">Menu Utama</span>
        </div>

        <div class="relative rounded-2xl bg-white shadow-sm border border-slate-200 px-6 sm:px-10 py-8">
            <div class="absolute left-2.5 top-6 bottom-6 w-1.5 bg-[radial-gradient(circle,theme(colors.slate.300)_1.4px,transparent_1.6px)] bg-[length:100%_14px] opacity-70"></div>
            <div class="absolute right-2.5 top-6 bottom-6 w-1.5 bg-[radial-gradient(circle,theme(colors.slate.300)_1.4px,transparent_1.6px)] bg-[length:100%_14px] opacity-70"></div>

            @livewire('components::launcher')
        </div>
    </div>
@endsection
{{-- @extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            @livewire('components::launcher')
        </div>
    </div>
@endsection --}}
