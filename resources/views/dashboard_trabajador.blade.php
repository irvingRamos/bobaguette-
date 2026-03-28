<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Inicio (Trabajador)</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
</head>
<body class="bg-[#E7DCC8] min-h-screen font-sans antialiased overflow-hidden"
      x-data="{ openLogout: false, showWarning: {{ session('warning') ? 'true' : 'false' }} }"
      x-init="if(showWarning) setTimeout(() => { showWarning = false; }, 5000)">

    {{-- TOAST ADVERTENCIA (NÚMEROS ROJOS) --}}
    @if(session('warning'))
    <div x-show="showWarning" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="toast-in fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-[#7c1c1c] text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(124,28,28,0.45)] border border-red-400/30 overflow-hidden">
            <div class="absolute inset-0 bg-red-500/10 animate-pulse pointer-events-none"></div>
            <div class="relative w-8 h-8 rounded-xl bg-red-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="relative">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-red-300 leading-none mb-0.5">Alerta de Stock</p>
                <p class="text-[13px] font-bold leading-none">{{ session('warning') }}</p>
            </div>
            <div class="absolute bottom-0 left-0 h-[3px] w-full bg-red-900/40 overflow-hidden rounded-b-2xl">
                <div class="h-full bg-red-400/80 rounded-full" style="animation: shrink 5s linear forwards;"></div>
            </div>
        </div>
    </div>
    @endif

    @php
        $lowStockCount = \App\Models\Insumo::whereColumn('cantidad', '<=', 'nivel_minimo')->count();
    @endphp

    <div class="fixed top-10 right-10 z-50 flex items-center gap-8 animate-fade-in">
        <div class="flex flex-col items-end">
            <span class="text-[10px] text-[#004225]/30 font-black uppercase tracking-[0.3em] mb-2">{{ now()->format('H:i | d/m/Y') }}</span>
            <div class="flex items-center gap-4 bg-white/40 backdrop-blur-xl px-5 py-2.5 rounded-[24px] border border-white/60 shadow-[0_10px_30px_rgba(0,0,0,0.03)] transition-all hover:bg-white/60 group">
                <div class="text-right">
                    <span class="block text-[14px] font-bold italic text-[#004225] leading-none group-hover:translate-x-[-2px] transition-transform">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] text-[#004225]/50 font-black uppercase tracking-[0.15em] mt-1.5 block">Cajero • Turno {{ auth()->user()->turno ?? 'Matutino' }}</span>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#004225] to-[#00311c] flex items-center justify-center text-white text-[12px] font-black shadow-lg shadow-[#004225]/20 group-hover:scale-105 transition-transform">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
        </div>

        <button @click="openLogout = true" 
                class="w-16 h-16 bg-white/60 backdrop-blur-xl rounded-[28px] border border-white/80 shadow-[0_15px_35px_rgba(0,0,0,0.05)] flex items-center justify-center text-gray-400 hover:text-red-500 hover:shadow-[0_20px_40px_rgba(239,68,68,0.15)] hover:bg-white/90 active:scale-90 transition-all group" 
                title="Cerrar Sesión">
            <svg class="w-7 h-7 group-hover:rotate-12 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </button>
    </div>

    <main class="relative flex flex-col items-center justify-center min-h-screen px-10">
        
        <div class="absolute inset-0 flex items-center justify-center z-0 select-none pointer-events-none overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-[#E7DCC8]/0 via-[#E7DCC8]/40 to-[#E7DCC8] z-10"></div>
            <img src="{{ asset('img/boba-chan.png') }}" 
                 alt="Boba-chan" 
                 class="w-[720px] opacity-[0.3] grayscale mix-blend-multiply contrast-[1.15] transform translate-y-16 scale-125 rotate-[-2deg] animate-float">
        </div>

        <div class="z-20 w-full max-w-7xl">
            <div class="text-center mb-24 animate-fade-in-up">
                <h2 class="text-[10rem] font-bold text-[#004225] italic tracking-[-0.08em] drop-shadow-[0_15px_30px_rgba(0,66,37,0.12)] leading-[0.8] mb-8">B&baguette.</h2>
                <div class="flex items-center justify-center gap-6">
                    <div class="h-[2px] w-16 bg-gradient-to-r from-transparent to-[#004225]/30"></div>
                    <p class="text-[#004225] font-black tracking-[1em] uppercase text-[14px] opacity-70">Punto de Venta</p>
                    <div class="h-[2px] w-16 bg-gradient-to-l from-transparent to-[#004225]/30"></div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 max-w-6xl mx-auto px-4">
                {{-- ABRIR MENÚ --}}
                <a href="{{ route('menu.index') }}" class="group relative bg-white/50 backdrop-blur-2xl p-12 rounded-[60px] border border-white/80 shadow-[0_30px_60px_rgba(0,0,0,0.04)] hover:shadow-[0_40px_80px_rgba(0,66,37,0.15)] transition-all duration-700 hover:-translate-y-5 flex flex-col items-center justify-center overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-[#004225]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="w-24 h-24 bg-[#004225]/5 rounded-[40px] flex items-center justify-center mb-10 group-hover:bg-[#004225] group-hover:rotate-[12deg] group-hover:scale-110 transition-all duration-700 shadow-sm relative z-10">
                        <svg class="w-11 h-11 text-[#004225] group-hover:text-white transition-colors duration-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="relative z-10 block text-[12px] font-black text-[#004225] uppercase tracking-[0.4em] group-hover:tracking-[0.5em] group-hover:scale-105 transition-all duration-700">Vender</span>
                </a>

                {{-- INVENTARIO --}}
                <a href="{{ route('inventario.index') }}" class="group relative bg-white/50 backdrop-blur-2xl p-12 rounded-[60px] border border-white/80 shadow-[0_30px_60px_rgba(0,0,0,0.04)] hover:shadow-[0_40px_80px_rgba(0,66,37,0.15)] transition-all duration-700 hover:-translate-y-5 flex flex-col items-center justify-center overflow-hidden">
                    @if($lowStockCount > 0)
                        <div class="absolute top-10 right-10 flex h-8 w-8 z-30">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-8 w-8 bg-red-500 text-[12px] text-white font-black items-center justify-center border-2 border-white shadow-lg shadow-red-500/30">{{ $lowStockCount }}</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-[#004225]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="w-24 h-24 bg-[#004225]/5 rounded-[40px] flex items-center justify-center mb-10 group-hover:bg-[#004225] group-hover:rotate-[-12deg] group-hover:scale-110 transition-all duration-700 shadow-sm relative z-10">
                        <svg class="w-11 h-11 text-[#004225] group-hover:text-white transition-colors duration-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4m8 4v6"/></svg>
                    </div>
                    <span class="relative z-10 block text-[12px] font-black text-[#004225] uppercase tracking-[0.4em] group-hover:tracking-[0.5em] group-hover:scale-105 transition-all duration-700">Stock</span>
                </a>

                {{-- PROMOCIONES --}}
                <a href="{{ route('promociones.index') }}" class="group relative bg-white/50 backdrop-blur-2xl p-12 rounded-[60px] border border-white/80 shadow-[0_30px_60px_rgba(0,0,0,0.04)] hover:shadow-[0_40px_80px_rgba(255,75,75,0.15)] transition-all duration-700 hover:-translate-y-5 flex flex-col items-center justify-center overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-[#ff4b4b]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="w-24 h-24 bg-[#ff4b4b]/5 rounded-[40px] flex items-center justify-center mb-10 group-hover:bg-[#ff4b4b] group-hover:rotate-[12deg] group-hover:scale-110 transition-all duration-700 shadow-sm relative z-10">
                        <svg class="w-11 h-11 text-[#ff4b4b] group-hover:text-white transition-colors duration-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <span class="relative z-10 block text-[12px] font-black text-[#004225] uppercase tracking-[0.4em] group-hover:tracking-[0.5em] group-hover:scale-105 transition-all duration-700">Promos</span>
                </a>

                {{-- MI CORTE --}}
                <a href="{{ route('corte.index') }}" class="group relative bg-white/50 backdrop-blur-2xl p-12 rounded-[60px] border border-white/80 shadow-[0_30px_60px_rgba(0,0,0,0.04)] hover:shadow-[0_40px_80px_rgba(232,160,0,0.15)] transition-all duration-700 hover:-translate-y-5 flex flex-col items-center justify-center overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-[#e8a000]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <div class="w-24 h-24 bg-[#e8a000]/5 rounded-[40px] flex items-center justify-center mb-10 group-hover:bg-[#e8a000] group-hover:rotate-[-12deg] group-hover:scale-110 transition-all duration-700 shadow-sm relative z-10">
                        <svg class="w-11 h-11 text-[#e8a000] group-hover:text-white transition-colors duration-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="relative z-10 block text-[12px] font-black text-[#004225] uppercase tracking-[0.4em] group-hover:tracking-[0.5em] group-hover:scale-105 transition-all duration-700">Corte</span>
                </a>
            </div>
        </div>
        
    </main>

    <style>
        @keyframes float {
            0%, 100% { transform: translate(0, 4rem) rotate(-2deg) scale(1.25); }
            50% { transform: translate(0, 2rem) rotate(0deg) scale(1.3); }
        }
        .animate-float { animation: float 12s ease-in-out infinite; }
        .animate-fade-in { animation: fadeIn 1s ease-out forwards; }
        .animate-fade-in-up { animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- Modal confirmación logout --}}
    <div x-show="openLogout"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm"
         @keydown.escape.window="openLogout = false">

        <div x-show="openLogout"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl p-10 w-full max-w-sm text-center shadow-2xl mx-4"
             @click.stop>

            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>

            <h3 class="text-xl font-bold italic text-[#004225] mb-1">¿Cerrar sesión?</h3>
            <p class="text-xs text-gray-400 font-medium mb-8">Tu turno ({{ auth()->user()->turno }}) quedará registrado.</p>

            <div class="flex gap-3">
                <button @click="openLogout = false"
                        class="flex-1 py-3 text-xs font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-red-500 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-600 active:scale-95 transition-all">
                        Salir
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
