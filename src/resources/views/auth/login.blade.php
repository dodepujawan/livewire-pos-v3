<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POPS Pro</title>
    @vite('resources/css/app.css')
    <style>
        /* Ambient slow background drift */
        @keyframes ambientDrift1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(40px, 30px) scale(1.12); }
        }
        @keyframes ambientDrift2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-35px, -35px) scale(1.08); }
        }
        @keyframes rotateSlow {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        @keyframes rotateReverse {
            from { transform: rotate(360deg); }
            to   { transform: rotate(0deg); }
        }
        @keyframes particleFloat {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0.2;
            }
            50% {
                transform: translateY(-28px) translateX(12px);
                opacity: 0.75;
            }
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50%      { transform: scale(1.6); opacity: 0.85; }
        }

        /* Smooth page entrance animation */
        @keyframes cardEntrance {
            0% {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes contentFadeIn {
            0% {
                opacity: 0;
                transform: translateY(8px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ambient-blob-1 {
            animation: ambientDrift1 20s ease-in-out infinite;
            filter: blur(80px);
        }
        .ambient-blob-2 {
            animation: ambientDrift2 24s ease-in-out infinite;
            filter: blur(90px);
        }
        .ambient-blob-3 {
            animation: ambientDrift1 18s ease-in-out infinite reverse;
            filter: blur(75px);
        }

        .spin-slow {
            animation: rotateSlow 80s linear infinite;
        }
        .spin-slow-reverse {
            animation: rotateReverse 95s linear infinite;
        }

        .particle-1 { animation: particleFloat 7s ease-in-out infinite; }
        .particle-2 { animation: particleFloat 9s ease-in-out infinite 2s; }
        .particle-3 { animation: particleFloat 8s ease-in-out infinite 4s; }
        .particle-4 { animation: particleFloat 10s ease-in-out infinite 1s; }
        .particle-5 { animation: particleFloat 6.5s ease-in-out infinite 3.5s; }

        .pulse-dot-1 { animation: pulseDot 4s ease-in-out infinite; }
        .pulse-dot-2 { animation: pulseDot 5s ease-in-out infinite 2s; }

        .login-card {
            animation: cardEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .fade-stagger-1 {
            animation: contentFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.15s backwards;
        }
        .fade-stagger-2 {
            animation: contentFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.25s backwards;
        }
        .fade-stagger-3 {
            animation: contentFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.35s backwards;
        }

        @media (prefers-reduced-motion: reduce) {
            .ambient-blob-1, .ambient-blob-2, .ambient-blob-3,
            .spin-slow, .spin-slow-reverse,
            .particle-1, .particle-2, .particle-3, .particle-4, .particle-5,
            .pulse-dot-1, .pulse-dot-2,
            .login-card, .fade-stagger-1, .fade-stagger-2, .fade-stagger-3 {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden bg-slate-950 text-slate-100 selection:bg-amber-500 selection:text-slate-950">

    <!-- Animated Ambient Background Layer -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none select-none" aria-hidden="true">
        <!-- Glowing Blobs -->
        <div class="ambient-blob-1 absolute -top-28 -left-28 w-[550px] h-[550px] rounded-full bg-amber-500/20"></div>
        <div class="ambient-blob-2 absolute -bottom-36 -right-24 w-[600px] h-[600px] rounded-full bg-indigo-600/25"></div>
        <div class="ambient-blob-3 absolute top-1/3 right-1/4 w-[400px] h-[400px] rounded-full bg-amber-600/15"></div>

        <!-- Decorative Orbiting Accent Rings -->
        <div class="spin-slow absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full border border-dashed border-amber-400/10 opacity-70"></div>
        <div class="spin-slow-reverse absolute -top-20 -right-20 w-[440px] h-[440px] rounded-full border border-amber-500/10 opacity-60"></div>
        <div class="spin-slow absolute -bottom-48 -left-48 w-[650px] h-[650px] rounded-full border border-dashed border-indigo-400/10 opacity-70"></div>

        <!-- Floating ambient sparkles / particles -->
        <div class="particle-1 absolute top-[20%] left-[15%] w-2 h-2 rounded-full bg-amber-400/80 shadow-[0_0_12px_rgba(251,191,36,0.8)]"></div>
        <div class="particle-2 absolute top-[70%] left-[18%] w-1.5 h-1.5 rounded-full bg-amber-300/80 shadow-[0_0_10px_rgba(251,191,36,0.6)]"></div>
        <div class="particle-3 absolute top-[28%] right-[16%] w-2.5 h-2.5 rounded-full bg-indigo-400/80 shadow-[0_0_14px_rgba(129,140,248,0.7)]"></div>
        <div class="particle-4 absolute top-[75%] right-[22%] w-1.5 h-1.5 rounded-full bg-amber-400/80 shadow-[0_0_8px_rgba(251,191,36,0.6)]"></div>
        <div class="particle-5 absolute top-[14%] right-[32%] w-1 h-1 rounded-full bg-white/90 shadow-[0_0_8px_rgba(255,255,255,0.8)]"></div>

        <!-- Gentle Pulsing Nodes -->
        <div class="pulse-dot-1 absolute top-1/4 left-[28%] w-2 h-2 rounded-full bg-amber-400/40 blur-[1px]"></div>
        <div class="pulse-dot-2 absolute bottom-1/3 right-[28%] w-2 h-2 rounded-full bg-indigo-400/40 blur-[1px]"></div>
    </div>

    <!-- Subtle Grid Pattern overlay -->
    <div class="absolute inset-0 opacity-[0.035] pointer-events-none"
         style="background-image: radial-gradient(rgba(255, 255, 255, 0.9) 1px, transparent 1px); background-size: 28px 28px;">
    </div>

    <div class="login-card relative w-full max-w-md mx-4 z-10">

        <!-- Glass card -->
        <div class="relative bg-slate-900/60 backdrop-blur-2xl border border-white/10 rounded-2xl shadow-2xl shadow-black/40 p-8 sm:p-9 transition-all duration-300">

            <!-- Logo & Title Section -->
            <div class="text-center mb-8 fade-stagger-1">
                <div class="inline-flex items-center justify-center h-20 w-20 rounded-2xl mb-4
                            bg-gradient-to-br from-amber-400/20 via-amber-500/10 to-transparent
                            border border-amber-400/30 shadow-[0_0_25px_rgba(251,191,36,0.15)]
                            transition-transform duration-300 hover:scale-105">
                    <img
                        src="{{ asset('gambar/pops_only.png') }}"
                        alt="POPS Logo"
                        class="h-18 w-18 object-contain rounded-xl"
                    >
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-white">
                    POPS <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400 align-middle ml-0.5">Pro</span>
                </h2>
                <p class="text-sm font-medium text-slate-400 mt-1">Cashier &amp; Ledger</p>
            </div>

            @if(session('error'))
                <div class="fade-stagger-1 flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-300 text-sm p-3.5 mb-6 rounded-xl">
                    <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}" id="loginForm" class="space-y-5 fade-stagger-2">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block mb-1.5 text-xs font-semibold uppercase tracking-wider text-slate-300">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" required autofocus autocomplete="email"
                            class="w-full bg-slate-950/50 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500
                                   focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400/50
                                   transition duration-200"
                            placeholder="nama@perusahaan.com">
                    </div>
                </div>

                <!-- Password Input with Toggle -->
                <div>
                    <label for="password" class="block mb-1.5 text-xs font-semibold uppercase tracking-wider text-slate-300">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="w-full bg-slate-950/50 border border-white/10 rounded-xl pl-10 pr-11 py-2.5 text-sm text-white placeholder-slate-500
                                   focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400/50
                                   transition duration-200"
                            placeholder="••••••••">
                        <button type="button" id="togglePassword" aria-label="Tampilkan / Sembunyikan Password"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-amber-400 transition-colors duration-150 focus:outline-none">
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeSlashIcon" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-sm pt-0.5">
                    <label class="flex items-center gap-2.5 text-slate-300 cursor-pointer select-none group">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-white/20 bg-slate-950/50 text-amber-500 focus:ring-amber-400/50 focus:ring-offset-0 transition cursor-pointer">
                        <span class="text-sm group-hover:text-white transition-colors">Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="w-full relative flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-semibold py-2.5 px-4 rounded-xl
                           transition-all duration-200 shadow-lg shadow-amber-500/20 hover:shadow-amber-500/35 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] disabled:opacity-75 disabled:pointer-events-none">
                    <span id="btnText">Masuk ke Sistem</span>
                    <svg id="btnSpinner" class="w-4 h-4 animate-spin hidden text-slate-950" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </form>
        </div>

        <p class="fade-stagger-3 text-center text-slate-500 text-xs mt-6">
            &copy; {{ date('Y') }} Sistem Point of Sale. Semua hak dilindungi.
        </p>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIcon.classList.toggle('hidden', isPassword);
                eyeSlashIcon.classList.toggle('hidden', !isPassword);
            });
        }

        // Smooth submit loading state
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', () => {
                submitBtn.disabled = true;
                btnText.textContent = 'Memproses...';
                btnSpinner.classList.remove('hidden');
            });
        }
    </script>
</body>
</html>
