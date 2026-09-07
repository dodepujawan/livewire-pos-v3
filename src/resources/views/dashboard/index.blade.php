@extends('layouts.app')

@section('content')
    <div class="relative mx-auto max-w-[1600px]">
        <div class="mb-3 flex items-center gap-2 px-1">
            <span class="h-1.5 w-1.5 rounded-full bg-[#D4AF37]"></span>
            <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-slate-400">Menu Utama</span>
        </div>

        <div class="relative rounded-2xl border border-slate-200/90 bg-white/80 px-3 py-4 shadow-[0_12px_35px_-28px_rgba(15,23,42,0.45)] sm:px-6 sm:py-6 lg:px-8 lg:py-7">
            <div class="pointer-events-none absolute left-2 top-5 bottom-5 w-px bg-gradient-to-b from-transparent via-slate-200 to-transparent sm:left-3"></div>
            <div class="pointer-events-none absolute right-2 top-5 bottom-5 w-px bg-gradient-to-b from-transparent via-slate-200 to-transparent sm:right-3"></div>

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
