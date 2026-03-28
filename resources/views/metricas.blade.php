<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Métricas</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased" x-data="{ openLogout: false, showWarning: {{ session('warning') ? 'true' : 'false' }} }" x-init="if(showWarning) setTimeout(() => { showWarning = false; }, 5000)">

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
                            'Corte de Caja' => route('corte.index'),
                        ];

                        if ($user->isAdmin()) {
                            $navItems['Promociones'] = route('promociones.index');
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
    <main class="max-w-5xl mx-auto px-8 py-10">

        {{-- Cards superiores --}}
        <div class="grid grid-cols-3 gap-4 mb-6">

            {{-- Ventas totales --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-[13px] font-semibold text-gray-500">Ventas totales</p>
                    <svg class="w-5 h-5 text-[#e8a000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[32px] font-black text-[#e8a000] leading-none">${{ number_format($ventasTotales, 2) }}</p>
                <p class="text-[10px] text-gray-400 font-medium mt-2">Últimos 7 días</p>
            </div>

            {{-- Transacciones --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-[13px] font-semibold text-gray-500">Transacciones</p>
                    <svg class="w-5 h-5 text-[#004225]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-[32px] font-black text-[#004225] leading-none">{{ $totalTransacciones }}</p>
                <p class="text-[10px] text-gray-400 font-medium mt-2">Órdenes procesadas</p>
            </div>

            {{-- Turno destacado --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-[13px] font-semibold text-gray-500">Turno destacado</p>
                    <svg class="w-5 h-5 text-[#e8a000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-[32px] font-black text-[#004225] leading-none">{{ $turnoDestacado?->turno ?? '—' }}</p>
                <p class="text-[10px] text-gray-400 font-medium mt-2">${{ number_format($turnoDestacado?->suma ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Gráficas --}}
        <div class="grid grid-cols-2 gap-4">

            {{-- Ventas por día --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
                <p class="text-[14px] font-bold text-[#004225] mb-1">Ventas por día</p>
                <p class="text-[10px] text-gray-400 font-medium mb-5">Últimos 7 días</p>
                <canvas id="chartVentas" height="180"></canvas>
            </div>

            {{-- Métodos de pago --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
                <p class="text-[14px] font-bold text-[#004225] mb-1">Métodos de pago</p>
                <p class="text-[10px] text-gray-400 font-medium mb-5">Distribución de transacciones</p>
                <canvas id="chartMetodos" height="180"></canvas>
            </div>
        </div>
    </main>

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

    {{-- Charts JS --}}
    <script>
        // Datos desde PHP
        const ventasDias   = @json($ventasPorDia);
        const metodosPago  = @json($metodosPago);

        // ── Gráfica de barras — Ventas por día ────────────────
        new Chart(document.getElementById('chartVentas'), {
            type: 'bar',
            data: {
                labels: ventasDias.map(v => v.dia),
                datasets: [{
                    data: ventasDias.map(v => v.total),
                    backgroundColor: '#e8a000',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Instrument Sans', size: 11 } } },
                    y: { grid: { color: '#f0ece4' }, ticks: { font: { family: 'Instrument Sans', size: 11 } } }
                }
            }
        });

        // ── Gráfica de pay — Métodos de pago ──────────────────
        const labels  = Object.keys(metodosPago).length ? Object.keys(metodosPago) : ['Efectivo', 'Tarjeta', 'Transferencia'];
        const valores = Object.values(metodosPago).length ? Object.values(metodosPago) : [0, 0, 0];

        new Chart(document.getElementById('chartMetodos'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: ['#e8a000', '#004225', '#d4c9b0'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { font: { family: 'Instrument Sans', size: 12 }, padding: 16 }
                    }
                }
            }
        });
    </script>

</body>
</html>