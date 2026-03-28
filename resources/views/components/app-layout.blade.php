<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bobaguette' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }

        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }

        /* Toasts */
        @keyframes shrink { from { width: 100%; } to { width: 0%; } }
        @keyframes toastBounceIn {
            0%   { opacity: 0; transform: translateY(-60px) scale(0.7); }
            55%  { opacity: 1; transform: translateY(10px) scale(1.04); }
            75%  { transform: translateY(-6px) scale(0.98); }
            90%  { transform: translateY(4px) scale(1.01); }
            100% { transform: translateY(0) scale(1); }
        }
        .toast-in { animation: toastBounceIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        {{ $extraStyles ?? '' }}
    </style>
    {{ $head ?? '' }}
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased"
      x-data="{
        openLogout: false,
        showSuccess:  {{ session('success')  ? 'true' : 'false' }},
        showEliminar: {{ session('eliminar') ? 'true' : 'false' }},
      }"
      x-init="
        if(showSuccess)  setTimeout(() => showSuccess  = false, 4000);
        if(showEliminar) setTimeout(() => showEliminar = false, 4000);
      ">

    {{-- ── TOAST ÉXITO ── --}}
    <div x-show="showSuccess"
         class="toast-in fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-[#004225] text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(0,66,37,0.5)] border border-white/10 overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-60 leading-none mb-0.5">Listo</p>
                <p class="text-[13px] font-bold leading-none">{{ session('success') }}</p>
            </div>
            <div class="absolute bottom-0 left-0 h-[3px] w-full bg-white/20 overflow-hidden rounded-b-2xl">
                <div class="h-full bg-white/70 rounded-full" style="animation: shrink 4s linear forwards;"></div>
            </div>
        </div>
    </div>

    {{-- ── TOAST ELIMINAR ── --}}
    <div x-show="showEliminar"
         class="toast-in fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-[#1a0505] text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(180,0,0,0.45)] border border-red-900/40 overflow-hidden">
            <div class="absolute inset-0 bg-red-600/15 blur-xl pointer-events-none"></div>
            <div class="relative w-8 h-8 rounded-xl bg-red-500/30 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div class="relative">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-red-400 leading-none mb-0.5">Eliminado</p>
                <p class="text-[13px] font-bold leading-none">{{ session('eliminar') }}</p>
            </div>
            <div class="absolute bottom-0 left-0 h-[3px] w-full bg-red-900/40 overflow-hidden rounded-b-2xl">
                <div class="h-full bg-red-500/80 rounded-full" style="animation: shrink 4s linear forwards;"></div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         HEADER COMPARTIDO
    ══════════════════════════════════════ --}}
    <header class="bg-white border-b border-gray-100 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <div class="max-w-5xl mx-auto px-8">

            {{-- Fila superior: logo · hora · usuario --}}
            <div class="py-4 flex justify-between items-center">
                <a href="{{ route('dashboard') }}"
                   class="text-[28px] font-bold text-[#004225] italic tracking-tighter leading-none hover:opacity-80 transition-opacity">
                    B&baguette.
                </a>
                <div class="flex items-center gap-5">
                    <span class="text-[11px] font-bold text-[#004225]/50 tabular-nums border-r border-gray-200 pr-5">
                        {{ now()->format('H:i d/m/Y') }}
                    </span>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-[9px] font-bold uppercase text-[#004225]/40 leading-none tracking-widest">
                                Turno {{ auth()->user()->turno ?? 'Matutino' }}
                            </p>
                            <p class="text-[13px] font-bold italic text-[#004225] leading-tight">
                                {{ auth()->user()->name }}
                            </p>
                        </div>
                        <button @click="openLogout = true"
                                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-red-50 text-[#004225]/40 hover:text-red-500 transition-all">
                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Nav — detección automática de ruta activa --}}
            <div class="pb-4 flex justify-center">
                <nav class="flex items-center gap-0.5 bg-gray-100/70 p-1 rounded-[14px]">
                    @php
                        $navItems = [
                            'Menú'          => ['route' => null,                    'active' => false],
                            'Promociones'   => ['route' => null,                    'active' => false],
                            'Métricas'      => ['route' => null,                    'active' => false],
                            'Inventario'    => ['route' => route('inventario.index'),'active' => request()->routeIs('inventario.*')],
                            'Corte de Caja' => ['route' => null,                    'active' => false],
                            'Usuarios'      => ['route' => route('usuarios.index'), 'active' => request()->routeIs('usuarios.*')],
                        ];
                    @endphp
                    @foreach($navItems as $label => $item)
                        <a href="{{ $item['route'] ?? '#' }}"
                           class="px-4 py-2 text-[12px] font-bold rounded-[10px] transition-all whitespace-nowrap
                                  {{ $item['active']
                                     ? 'bg-white text-[#004225] shadow-sm'
                                     : 'text-gray-400 hover:text-[#004225]/70' }}
                                  {{ !$item['route'] ? 'cursor-not-allowed opacity-50' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- ── CONTENIDO DE CADA VISTA ── --}}
    {{ $slot }}

    {{-- ══════════════════════════════════════
         MODAL — LOGOUT (compartido)
    ══════════════════════════════════════ --}}
    <div x-show="openLogout" x-cloak
         class="fixed inset-0 z-[80] flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openLogout = false">
        <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Cerrar sesión?</h3>
            <p class="text-[11px] text-gray-400 font-medium mb-6">Tu turno quedará registrado.</p>
            <div class="flex gap-3">
                <button @click="openLogout = false"
                        class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">
                        Salir
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>