<div>
    {{-- Overlay: klik di luar sidebar untuk menutup --}}
    <div
        x-show="open"
        x-transition.opacity
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
    ></div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col shadow-2xl transform transition-transform duration-300
               bg-gradient-to-b from-[#0f172a] via-[#1a2a6c] to-[#0f172a] text-gray-200
               border-r border-white/10"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Header / Brand --}}
        <div class="relative shrink-0 px-4 pt-4 pb-3 border-b border-white/10">
            {{-- subtle gold glow behind logo --}}
            <div class="pointer-events-none absolute -top-6 left-3 h-20 w-20 rounded-full bg-amber-400/10 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Logo frame --}}
                    <div class="relative shrink-0 flex h-11 w-11 items-center justify-center rounded-xl
                                bg-gradient-to-br from-amber-400/20 to-transparent
                                border border-amber-400/30 shadow-[0_0_16px_rgba(251,191,36,0.15)]">
                        <img
                            src="{{ asset('gambar/pops_only.png') }}"
                            alt="POPS Logo"
                            class="h-8 w-8 object-contain"
                        >
                    </div>

                    <div class="min-w-0 leading-tight">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-base font-extrabold tracking-wide text-white">POPS</span>
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-amber-400/90">Pro</span>
                        </div>
                        <div class="text-[11px] font-medium text-amber-200/80 truncate">Cashier &amp; Ledger</div>
                        <div class="text-[10px] text-slate-400 truncate mt-0.5">Alan Mart</div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition"
                    aria-label="Tutup sidebar"
                >
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Menu --}}
        <div class="flex-1 overflow-y-auto px-3 py-4">
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Navigasi</p>
            @livewire('components::sidebar')
        </div>

        {{-- Footer --}}
        <div class="shrink-0 p-3 border-t border-white/10">
            {{-- dwebpro brand & WhatsApp support --}}
            <div class="flex items-center justify-between gap-2 rounded-xl
                        bg-gradient-to-r from-[#1a2a6c] via-[#1a2a6c] to-[#0f172a]
                        border border-amber-400/25 px-3 py-2.5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="shrink-0 h-9 w-9 rounded-lg bg-gradient-to-br from-amber-400/25 to-transparent
                                border border-amber-400/30 shadow-[0_0_12px_rgba(251,191,36,.12)]
                                flex items-center justify-center overflow-hidden">
                        <img
                            src="{{ asset('gambar/dwebpro.jpeg') }}"
                            alt="dwebpro"
                            class="h-7 w-7 object-contain"
                        >
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold text-white tracking-wide">dwebpro</div>
                        <div class="text-[10px] text-amber-400/70 -mt-0.5">Web Programming</div>
                    </div>
                </div>

                <a
                    href="https://wa.me/6285738828874"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="shrink-0 inline-flex items-center justify-center h-8 px-2.5 rounded-lg
                           bg-[#25D366] text-[#0f172a] font-medium text-xs
                           hover:bg-[#128C7E] hover:scale-105
                           transition-colors duration-200"
                    aria-label="Chat via WhatsApp"
                >
                    <i class="ti ti-brand-whatsapp text-lg leading-none"></i>
                </a>
            </div>

            {{-- <p class="mt-2 text-center text-[10px] text-slate-600 tracking-wide">POS SPA &middot; v1.0</p> --}}
        </div>
    </aside>
</div>
