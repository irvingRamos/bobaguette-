<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Corte de Caja</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased" x-data="{ openLogout: false, openGasto: false, deleteId: null, turnoGasto: 'Matutino', showSuccess: {{ session('success') ? 'true' : 'false' }}, showError: {{ session('error') ? 'true' : 'false' }}, showWarning: {{ session('warning') ? 'true' : 'false' }} }" x-init="if(showSuccess || showError || showWarning) setTimeout(() => { showSuccess = false; showError = false; showWarning = false; }, 5000)">

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

    {{-- TOAST ERROR --}}
    @if(session('error'))
    <div x-show="showError" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-red-600 text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(220,38,38,0.5)] border border-white/10 overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-60 leading-none mb-0.5">Error</p>
                <p class="text-[13px] font-bold leading-none">{{ session('error') }}</p>
            </div>
            <div class="absolute bottom-0 left-0 h-[3px] w-full bg-white/20 overflow-hidden rounded-b-2xl">
                <div class="h-full bg-white/70 rounded-full" style="animation: shrink 4s linear forwards;"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- TOAST ÉXITO --}}
    @if(session('success'))
    <div x-show="showSuccess" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-[#004225] text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(0,66,37,0.5)] border border-white/10 overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
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
    @endif

    {{-- HEADER --}}
    <header class="bg-white border-b border-gray-100 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <div class="max-w-5xl mx-auto px-8">
            <div class="py-4 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-[28px] font-bold text-[#004225] italic tracking-tighter leading-none">B&baguette.</a>
                <div class="flex items-center gap-5">
                    <span class="text-[11px] font-bold text-[#004225]/50 tabular-nums border-r border-gray-200 pr-5" x-data="{ timer: '' }" x-init="const update = () => { const d = new Date(); const h = d.getHours().toString().padStart(2, '0'); const m = d.getMinutes().toString().padStart(2, '0'); const day = d.getDate().toString().padStart(2, '0'); const month = (d.getMonth() + 1).toString().padStart(2, '0'); const year = d.getFullYear(); timer = h + ':' + m + ' ' + day + '/' + month + '/' + year; }; update(); setInterval(update, 1000);" x-text="timer"></span>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-[9px] font-bold uppercase text-[#004225]/40 leading-none tracking-widest">Turno {{ auth()->user()->turno ?? 'Matutino' }}</p>
                            <p class="text-[13px] font-bold italic text-[#004225] leading-tight">{{ auth()->user()->name }}</p>
                        </div>
                        <button @click="openLogout = true" class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-red-50 text-[#004225]/40 hover:text-red-500 transition-all">
                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="pb-4 flex justify-center">
                <nav class="flex items-center gap-0.5 bg-gray-100/70 p-1 rounded-[14px]">
                    @php
                        $user = auth()->user();
                        $lowStockCount = \App\Models\Insumo::whereColumn('cantidad', '<=', 'nivel_minimo')->count();
                        $navItems = [
                            'Menú'          => route('menu.index'),
                            'Inventario'    => route('inventario.index'),
                            'Promociones'   => route('promociones.index'),
                            'Corte de Caja' => route('corte.index'),
                        ];

                        if ($user->isAdmin()) {
                            $navItems['Métricas']    = route('metricas.index');
                            $navItems['Usuarios']    = route('usuarios.index');
                        }
                    @endphp
                    @foreach($navItems as $label => $href)
                        @php
                            $active = ($label === 'Menú'          && request()->routeIs('menu.*'))
                                   || ($label === 'Promociones'   && request()->routeIs('promociones.*'))
                                   || ($label === 'Métricas'      && request()->routeIs('metricas.*'))
                                   || ($label === 'Inventario'    && request()->routeIs('inventario.*'))
                                   || ($label === 'Corte de Caja' && request()->routeIs('corte.*'))
                                   || ($label === 'Usuarios'      && request()->routeIs('usuarios.*'));
                        @endphp
                        <a href="{{ $href }}" class="relative px-4 py-2 text-[12px] font-bold rounded-[10px] transition-all whitespace-nowrap {{ $active ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400 hover:text-[#004225]/70' }}">
                            {{ $label }}
                            @if($label === 'Inventario' && $lowStockCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[9px] text-white font-black items-center justify-center border border-white">{{ $lowStockCount }}</span>
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="max-w-5xl mx-auto px-8 py-8">

        {{-- Total general — card verde oscuro --}}
        <div class="bg-[#004225] rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-[#e8a000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-white font-bold text-[15px]">Total general</p>
            </div>
            <div class="grid grid-cols-4 gap-4">
                {{-- Efectivo --}}
                <div class="bg-[#003318] rounded-xl p-4">
                    <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">Efectivo</p>
                    <p class="text-[22px] font-black text-[#e8a000] leading-none">${{ number_format($totalEfectivo, 2) }}</p>
                    <p class="text-[10px] text-white/40 font-medium mt-1">{{ $transEfectivo }} transacciones</p>
                </div>
                {{-- Tarjeta --}}
                <div class="bg-[#003318] rounded-xl p-4">
                    <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">Tarjeta</p>
                    <p class="text-[22px] font-black text-[#e8a000] leading-none">${{ number_format($totalTarjeta, 2) }}</p>
                    <p class="text-[10px] text-white/40 font-medium mt-1">{{ $transTarjeta }} transacciones</p>
                </div>
                {{-- Transferencia --}}
                <div class="bg-[#003318] rounded-xl p-4">
                    <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">Transferencia</p>
                    <p class="text-[22px] font-black text-[#e8a000] leading-none">${{ number_format($totalTransferencia, 2) }}</p>
                    <p class="text-[10px] text-white/40 font-medium mt-1">{{ $transTransferencia }} transacciones</p>
                </div>
                {{-- Total --}}
                <div class="bg-[#003318] rounded-xl p-4">
                    <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">Total Neto</p>
                    <p class="text-[22px] font-black text-[#e8a000] leading-none">${{ number_format($totalGeneral, 2) }}</p>
                    <p class="text-[10px] text-white/40 font-medium mt-1">Descontando gastos</p>
                </div>
            </div>
        </div>

        {{-- Gastos del día --}}
        <div class="mb-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[15px] font-bold text-[#004225]">Gastos del día</p>
                    </div>
                    <p class="text-[16px] font-black text-red-500">-${{ number_format($totalGastos, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Cards por turno --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach(['Matutino', 'Vespertino', 'Parcial'] as $turno)
            @php $d = $datosTurnos[$turno]; @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)] overflow-hidden flex flex-col">
                {{-- Header turno --}}
                <div class="flex items-center justify-between px-5 pt-5 pb-4">
                    <p class="text-[15px] font-bold text-[#004225]">Turno {{ $turno }}</p>
                    <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg">
                        {{ $d['num_ventas'] }} ventas
                    </span>
                </div>

                {{-- Filas de métodos --}}
                <div class="px-5 space-y-3 pb-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#e8a000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[13px] font-semibold text-gray-600">Efectivo</p>
                        </div>
                        <p class="text-[13px] font-black text-[#e8a000]">${{ number_format($d['efectivo'], 2) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#004225]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <p class="text-[13px] font-semibold text-gray-600">Transferencia</p>
                        </div>
                        <p class="text-[13px] font-black text-[#e8a000]">${{ number_format($d['transferencia'], 2) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#004225]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <p class="text-[13px] font-semibold text-gray-600">Tarjeta</p>
                        </div>
                        <p class="text-[13px] font-black text-[#e8a000]">${{ number_format($d['tarjeta'], 2) }}</p>
                    </div>
                    <div class="flex items-center justify-between text-red-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[13px] font-semibold">Gastos</p>
                        </div>
                        <p class="text-[13px] font-black">-${{ number_format($d['gastos'], 2) }}</p>
                    </div>

                    {{-- Lista detallada de gastos (Solo para Admin) --}}
                    @if(auth()->user()->rol === 'Administrador' && count($d['lista_gastos']) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-50 space-y-2">
                        <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest mb-2">Detalle de Gastos</p>
                        @foreach($d['lista_gastos'] as $g)
                        <div class="flex items-center justify-between group">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-bold text-[#004225] leading-none">{{ $g->nombre }}</span>
                                @if($g->descripcion)
                                <span class="text-[9px] text-gray-400 leading-tight mt-0.5">{{ $g->descripcion }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-black text-red-500">-${{ number_format($g->monto, 2) }}</span>
                                <button @click="deleteId = {{ $g->id }}" class="p-1 text-gray-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Total turno --}}
                <div class="border-t border-gray-100 px-5 py-3 flex justify-between items-center bg-gray-50/50">
                    <p class="text-[13px] font-bold text-[#004225]">Total Neto</p>
                    <p class="text-[15px] font-black text-[#e8a000]">${{ number_format($d['total'], 2) }}</p>
                </div>

                {{-- Acciones --}}
                <div class="px-5 pb-5 pt-3 space-y-2">
                    <button @click="openGasto = true; turnoGasto = '{{ $turno }}'"
                            class="w-full py-2 border-2 border-red-100 text-red-500 text-[10px] font-black rounded-xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-1.5 uppercase tracking-tighter">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Gasto
                    </button>
                    <button onclick="imprimirCorte('{{ $turno }}')"
                            class="w-full py-2.5 bg-[#004225] text-white text-[11px] font-black rounded-xl hover:bg-[#00311c] transition-all flex items-center justify-center gap-2 shadow-sm uppercase tracking-widest">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Descargar corte
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    {{-- MODAL AGREGAR GASTO --}}
    <div x-show="openGasto" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openGasto = false">
        <div class="bg-white rounded-[24px] w-full max-w-sm overflow-hidden shadow-2xl" @click.stop>
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-[18px] font-bold text-[#004225] italic leading-tight">Registrar Gasto</h3>
                        <p class="text-[11px] text-gray-400 font-medium mt-1">Turno: <span x-text="turnoGasto" class="text-[#e8a000] font-black uppercase"></span></p>
                    </div>
                    <button @click="openGasto = false" class="text-gray-300 hover:text-[#004225] transition-colors text-2xl">×</button>
                </div>

                <form action="{{ route('corte.gasto') }}" method="POST" class="space-y-4" x-data="{ selectedInsumo: '' }">
                    @csrf
                    <input type="hidden" name="turno" :value="turnoGasto">
                    
                    <div>
                        <label class="block text-[11px] font-black text-[#004225]/40 uppercase tracking-widest mb-1.5 ml-1">Concepto / Insumo</label>
                        <div class="relative">
                            <input type="text" name="nombre" x-model="selectedInsumo" list="lista-insumos" placeholder="Ej. Leche, Basura..." required
                                   class="w-full bg-[#f5f4f1] border-none rounded-xl py-3 px-4 text-[13px] font-bold text-[#004225] outline-none focus:ring-2 focus:ring-[#004225]/10 transition-all placeholder:text-gray-300">
                            <datalist id="lista-insumos">
                                @foreach($insumos as $insumo)
                                    <option value="{{ $insumo->nombre }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- Campo dinámico para cantidad si el nombre coincide con un insumo --}}
                    <div x-show="[ @foreach($insumos as $insumo) '{{ $insumo->nombre }}', @endforeach ].includes(selectedInsumo)" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-amber-50 p-4 rounded-xl border border-amber-100">
                        <label class="block text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1.5">Cantidad a sumar al inventario</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="cantidad_insumo" placeholder="Ej. 14" step="0.01"
                                   class="flex-1 bg-white border-amber-200 rounded-lg py-2 px-3 text-[13px] font-bold text-amber-700 outline-none focus:ring-2 focus:ring-amber-500/20">
                            <span class="text-[11px] font-bold text-amber-600">Unidades</span>
                        </div>
                        <p class="text-[9px] text-amber-600/70 mt-2 italic">* Se detectó un insumo existente. Ingresa la cantidad para actualizar el stock automáticamente.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-[#004225]/40 uppercase tracking-widest mb-1.5 ml-1">Monto del Gasto ($)</label>
                        <input type="number" name="monto" step="0.01" placeholder="0.00" required
                               class="w-full bg-[#f5f4f1] border-none rounded-xl py-3 px-4 text-[13px] font-bold text-[#e8a000] outline-none focus:ring-2 focus:ring-[#e8a000]/10 transition-all placeholder:text-gray-300">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-[#004225]/40 uppercase tracking-widest mb-1.5 ml-1">Descripción (Opcional)</label>
                        <textarea name="descripcion" placeholder="Detalles adicionales..." rows="2"
                                  class="w-full bg-[#f5f4f1] border-none rounded-xl py-3 px-4 text-[13px] font-bold text-[#004225] outline-none focus:ring-2 focus:ring-[#004225]/10 transition-all placeholder:text-gray-300 resize-none"></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="openGasto = false"
                                class="flex-1 py-3 text-[11px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 rounded-xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 py-3 bg-[#004225] text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-[#00311c] transition-all shadow-md active:scale-95">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL ELIMINAR GASTO --}}
    <div x-show="deleteId" x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="deleteId = null">
        <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Eliminar gasto?</h3>
            <p class="text-[11px] text-gray-400 font-medium mb-6">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button @click="deleteId = null" class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                <form :action="'/corte/gasto/' + deleteId" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all shadow-lg active:scale-95">Eliminar</button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL LOGOUT --}}
    <div x-show="openLogout" x-cloak
         class="fixed inset-0 z-[80] flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openLogout = false">
        <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Cerrar sesión?</h3>
            <p class="text-[11px] text-gray-400 font-medium mb-6">Tu turno quedará registrado.</p>
            <div class="flex gap-3">
                <button @click="openLogout = false" class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">Salir</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    async function imprimirCorte(turno) {
        const datos = @json($datosTurnos);
        const d = datos[turno];
        const fecha = '{{ now()->format("d/m/Y H:i") }}';
        
        let texto = `
   BOBAGUETTE - CORTE
==========================
Turno: ${turno}
Fecha: ${fecha}
Ventas: ${d.num_ventas}
--------------------------
Efectivo:      $${parseFloat(d.efectivo).toFixed(2)}
Transferencia: $${parseFloat(d.transferencia).toFixed(2)}
Tarjeta:       $${parseFloat(d.tarjeta).toFixed(2)}
--------------------------
Gastos:       -$${parseFloat(d.gastos).toFixed(2)}
--------------------------
TOTAL NETO:    $${parseFloat(d.total).toFixed(2)}
==========================
\n\n\n`;

        // Intentar imprimir vía Web Bluetooth
        if (navigator.bluetooth) {
            try {
                const device = await navigator.bluetooth.requestDevice({
                    filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                });
                const server = await device.gatt.connect();
                const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                
                const encoder = new TextEncoder();
                const data = encoder.encode(texto);
                await characteristic.writeValue(data);
                
                alert('Imprimiendo corte via Bluetooth...');
                return;
            } catch (error) {
                console.log('Bluetooth print failed or cancelled:', error);
            }
        }

        // Fallback: Impresión de sistema
        const win = window.open('', '_blank');
        win.document.write('<pre>' + texto + '</pre>');
        win.document.close();
        win.print();
        win.close();
    }
    </script>

</body>
</html>