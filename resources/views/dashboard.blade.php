<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Inicio</title>
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

    <header class="bg-white/90 backdrop-blur-md sticky top-0 z-40 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-8 py-4 flex justify-between items-center">
            
            <a href="{{ route('dashboard') }}" class="hover:opacity-75 transition-opacity">
                <h1 class="text-2xl font-bold text-[#004225] italic tracking-tighter">B&baguette.</h1>
            </a>
            
            <nav class="flex items-center gap-2 bg-gray-100/50 p-1 rounded-xl">
                @php
                    $lowStockCount = \App\Models\Insumo::whereColumn('cantidad', '<=', 'nivel_minimo')->count();
                @endphp
                <a href="{{ route('menu.index') }}" 
                   class="px-5 py-2 text-sm font-bold transition-all {{ request()->routeIs('menu.*') ? 'bg-white text-[#004225] shadow-sm rounded-lg' : 'text-gray-400 hover:text-[#004225]' }}">
                   Menú
                </a>
                <a href="{{ route('promociones.index') }}" 
                   class="px-5 py-2 text-sm font-bold transition-all {{ request()->routeIs('promociones.*') ? 'bg-white text-[#004225] shadow-sm rounded-lg' : 'text-gray-400 hover:text-[#004225]' }}">
                   Promociones
                </a>
                <a href="{{ route('metricas.index') }}" 
                   class="px-5 py-2 text-sm font-bold transition-all {{ request()->routeIs('metricas.*') ? 'bg-white text-[#004225] shadow-sm rounded-lg' : 'text-gray-400 hover:text-[#004225]' }}">
                   Métricas
                </a>
                <a href="{{ route('inventario.index') }}" 
                   class="relative px-5 py-2 text-sm font-bold transition-all {{ request()->routeIs('inventario.*') ? 'bg-white text-[#004225] shadow-sm rounded-lg' : 'text-gray-400 hover:text-[#004225]' }}">
                   Inventario
                   @if($lowStockCount > 0)
                       <span class="absolute -top-1 -right-1 flex h-4 w-4">
                           <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                           <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[9px] text-white font-black items-center justify-center border border-white">{{ $lowStockCount }}</span>
                       </span>
                   @endif
                </a>
                <a href="{{ route('corte.index') }}" 
                   class="px-5 py-2 text-sm font-bold transition-all {{ request()->routeIs('corte.*') ? 'bg-white text-[#004225] shadow-sm rounded-lg' : 'text-gray-400 hover:text-[#004225]' }}">
                   Corte de Caja
                </a>
                <a href="{{ route('usuarios.index') }}" 
                   class="px-5 py-2 text-sm font-bold transition-all {{ request()->routeIs('usuarios.*') ? 'bg-white text-[#004225] shadow-sm rounded-lg' : 'text-gray-400 hover:text-[#004225]' }}">
                   Usuarios
                </a>
            </nav>

            <div class="flex items-center gap-4">
                <div class="flex flex-col items-end leading-none">
                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter mb-1">{{ now()->format('H:i | d/m/Y') }}</span>
                    <span class="italic font-bold text-[#004225] text-sm">{{ auth()->user()->name }}</span>
                </div>

                <button @click="openLogout = true" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Cerrar Sesión">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="relative flex flex-col items-center justify-center pt-20" style="height: calc(100vh - 80px);">
        
        <div class="absolute inset-0 flex items-center justify-center z-0 select-none pointer-events-none transform -translate-y-8">
            <img src="{{ asset('img/boba-chan.png') }}" 
                 alt="Boba-chan" 
                 class="w-[580px] opacity-[0.45] grayscale mix-blend-multiply contrast-125">
        </div>

        <div class="z-10 text-center">
            <h2 class="text-7xl font-bold text-[#004225] italic tracking-tighter drop-shadow-md">B&baguette.</h2>
            <p class="text-center text-[#004225] font-bold tracking-[0.6em] uppercase text-[14px] mt-4 opacity-90">Punto de Venta</p>
        </div>
        
    </main>

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
            <p class="text-xs text-gray-400 font-medium mb-8">Tu turno quedará registrado.</p>

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