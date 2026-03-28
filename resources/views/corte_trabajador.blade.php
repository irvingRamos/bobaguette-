<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bobaguette - Mi Corte de Caja</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }
        @keyframes shrink { from { width: 100%; } to { width: 0%; } }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased"
      x-data="{
        openLogout: false,
        openGasto: false,
        turnoGasto: '{{ $turnoActual }}',
        showSuccess: {{ session('success') ? 'true' : 'false' }},
        showError: {{ session('error') ? 'true' : 'false' }},

        // Ventas offline pendientes
        ventasOffline: [],
        totalOffline: 0,
        efectivoOffline: 0,
        tarjetaOffline: 0,
        transferenciaOffline: 0,
        countOffline: 0,

        async loadOfflineVentas() {
            if (window.getVentasPendientes) {
                const ventas = await window.getVentasPendientes();
                const turno = '{{ $turnoActual }}';
                const misTurno = ventas.filter(v => v.turno === turno);
                this.ventasOffline = misTurno;
                this.countOffline = misTurno.length;
                this.totalOffline = misTurno.reduce((s, v) => s + v.total, 0);
                this.efectivoOffline = misTurno.filter(v => v.metodo_pago === 'Efectivo').reduce((s, v) => s + v.total, 0);
                this.tarjetaOffline = misTurno.filter(v => v.metodo_pago === 'Tarjeta').reduce((s, v) => s + v.total, 0);
                this.transferenciaOffline = misTurno.filter(v => v.metodo_pago === 'Transferencia').reduce((s, v) => s + v.total, 0);
            }
        }
      }"
      x-init="
        if(showSuccess || showError) setTimeout(() => { showSuccess = false; showError = false; }, 4000);
        await loadOfflineVentas();
        window.addEventListener('offline-sync-success', () => { loadOfflineVentas(); });
        setInterval(() => { loadOfflineVentas(); }, 5000);
      ">

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
        </div>
    </div>
    @endif

    {{-- HEADER --}}
    <header class="bg-white border-b border-gray-100 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <div class="max-w-5xl mx-auto px-8">
            <div class="py-4 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-[28px] font-bold text-[#004225] italic tracking-tighter leading-none">B&baguette.</a>
                <div class="flex items-center gap-5">
                    <span class="text-[11px] font-bold text-[#004225]/50 tabular-nums border-r border-gray-200 pr-5"
                          x-data="{ timer: '' }"
                          x-init="const update = () => { const d = new Date(); timer = d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0') + ' ' + d.getDate().toString().padStart(2,'0') + '/' + (d.getMonth()+1).toString().padStart(2,'0') + '/' + d.getFullYear(); }; update(); setInterval(update, 1000);"
                          x-text="timer"></span>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-[9px] font-bold uppercase text-[#004225]/40 leading-none tracking-widest">Turno {{ $turnoActual }}</p>
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
                        $navItems = [
                            'Menú'          => route('menu.index'),
                            'Inventario'    => route('inventario.index'),
                            'Promociones'   => route('promociones.index'),
                            'Corte de Caja' => route('corte.index'),
                        ];
                        if ($user->isAdmin()) {
                            $navItems['Métricas'] = route('metricas.index');
                            $navItems['Usuarios'] = route('usuarios.index');
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
                        <a href="{{ $href }}" class="px-4 py-2 text-[12px] font-bold rounded-[10px] transition-all whitespace-nowrap {{ $active ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400 hover:text-[#004225]/70' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="max-w-2xl mx-auto px-8 py-8">

        <div class="bg-white rounded-[32px] border border-gray-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden">
            {{-- Header card --}}
            <div class="bg-[#004225] px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                <p class="text-[11px] font-black text-white/40 uppercase tracking-[0.3em] mb-2">Resumen de mi turno</p>
                <h2 class="text-[32px] font-black text-[#e8a000] leading-none"
                    x-text="'$' + ({{ $datosTurno['total'] }} + totalOffline).toFixed(2)">
                    ${{ number_format($datosTurno['total'], 2) }}
                </h2>
                <p class="text-[12px] text-white/60 font-medium mt-2 italic">
                    Turno {{ $turnoActual }} •
                    <span x-text="{{ $datosTurno['num_ventas'] }} + countOffline + ' ventas'">{{ $datosTurno['num_ventas'] }} ventas</span>
                </p>
            </div>

            <div class="p-8 space-y-6">

                {{-- Aviso ventas pendientes offline --}}
                <div x-show="countOffline > 0" x-cloak
                     class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-black text-amber-700" x-text="countOffline + ' venta(s) pendiente(s) de sincronizar'"></p>
                        <p class="text-[10px] text-amber-600 font-medium">Se sincronizarán automáticamente al conectarte a internet</p>
                    </div>
                </div>

                {{-- Desglose por método --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-2xl p-4 text-center">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Efectivo</p>
                        <p class="text-[16px] font-black text-[#004225]"
                           x-text="'$' + ({{ $datosTurno['efectivo'] }} + efectivoOffline).toFixed(2)">
                            ${{ number_format($datosTurno['efectivo'], 2) }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-4 text-center">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Tarjeta</p>
                        <p class="text-[16px] font-black text-[#004225]"
                           x-text="'$' + ({{ $datosTurno['tarjeta'] }} + tarjetaOffline).toFixed(2)">
                            ${{ number_format($datosTurno['tarjeta'], 2) }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-4 text-center">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Transf.</p>
                        <p class="text-[16px] font-black text-[#004225]"
                           x-text="'$' + ({{ $datosTurno['transferencia'] }} + transferenciaOffline).toFixed(2)">
                            ${{ number_format($datosTurno['transferencia'], 2) }}
                        </p>
                    </div>
                </div>

                {{-- Gastos --}}
                <div class="bg-red-50 rounded-2xl p-6 border border-red-100/50">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-[14px] font-bold text-red-600">Gastos registrados</p>
                        </div>
                        <p class="text-[16px] font-black text-red-600">-${{ number_format($datosTurno['gastos'], 2) }}</p>
                    </div>
                    @if(count($datosTurno['lista_gastos']) > 0)
                    <div class="space-y-3">
                        @foreach($datosTurno['lista_gastos'] as $g)
                        <div class="flex items-center justify-between bg-white/60 p-3 rounded-xl border border-white">
                            <div class="flex flex-col">
                                <span class="text-[12px] font-bold text-[#004225]">{{ $g->nombre }}</span>
                                @if($g->descripcion)
                                <span class="text-[10px] text-gray-400">{{ $g->descripcion }}</span>
                                @endif
                            </div>
                            <span class="text-[12px] font-black text-red-500">-${{ number_format($g->monto, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-[11px] text-red-400 italic text-center py-2">No has registrado gastos en este turno.</p>
                    @endif
                </div>

                {{-- Botones de acción --}}
                <div class="grid grid-cols-2 gap-4 pt-4">
                    <button @click="openGasto = true"
                            class="py-4 bg-white border-2 border-red-100 text-red-500 text-[11px] font-black rounded-2xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2 uppercase tracking-widest shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Registrar Gasto
                    </button>
                    <button onclick="imprimirCorte()"
                            class="py-4 bg-[#004225] text-white text-[11px] font-black rounded-2xl hover:bg-[#00311c] transition-all flex items-center justify-center gap-2 uppercase tracking-widest shadow-lg shadow-[#004225]/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimir Ticket
                    </button>
                </div>
            </div>
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
                        <p class="text-[11px] text-gray-400 font-medium mt-1">Mi Turno: <span class="text-[#e8a000] font-black uppercase">{{ $turnoActual }}</span></p>
                    </div>
                    <button @click="openGasto = false" class="text-gray-300 hover:text-[#004225] transition-colors text-2xl">×</button>
                </div>
                <form action="{{ route('corte.gasto') }}" method="POST" class="space-y-4" x-data="{ selectedInsumo: '' }">
                    @csrf
                    <input type="hidden" name="turno" value="{{ $turnoActual }}">
                    <div>
                        <label class="block text-[11px] font-black text-[#004225]/40 uppercase tracking-widest mb-1.5 ml-1">Concepto / Insumo</label>
                        <input type="text" name="nombre" x-model="selectedInsumo" list="lista-insumos" placeholder="Ej. Leche, Basura..." required
                               class="w-full bg-[#f5f4f1] border-none rounded-xl py-3 px-4 text-[13px] font-bold text-[#004225] outline-none focus:ring-2 focus:ring-[#004225]/10 transition-all placeholder:text-gray-300">
                        <datalist id="lista-insumos">
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->nombre }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-[#004225]/40 uppercase tracking-widest mb-1.5 ml-1">Monto del Gasto ($)</label>
                        <input type="number" name="monto" step="0.01" placeholder="0.00" required
                               class="w-full bg-[#f5f4f1] border-none rounded-xl py-3 px-4 text-[13px] font-bold text-[#e8a000] outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-[#004225]/40 uppercase tracking-widest mb-1.5 ml-1">Descripción</label>
                        <textarea name="descripcion" placeholder="Opcional..." rows="2"
                                  class="w-full bg-[#f5f4f1] border-none rounded-xl py-3 px-4 text-[13px] font-bold text-[#004225] outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="openGasto = false" class="flex-1 py-3 text-[11px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 rounded-xl transition-all">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-[#004225] text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-[#00311c] transition-all">Guardar</button>
                    </div>
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
    async function imprimirCorte() {
        const d = @json($datosTurno);
        const turno = '{{ $turnoActual }}';
        const fecha = new Date().toLocaleString();
        const usuario = '{{ auth()->user()->name }}';

        // Sumar ventas offline
        let extraTotal = 0, extraEfectivo = 0, extraTarjeta = 0, extraTransferencia = 0, extraCount = 0;
        if (window.getVentasPendientes) {
            const offline = await window.getVentasPendientes();
            const miTurno = offline.filter(v => v.turno === turno);
            extraCount = miTurno.length;
            extraTotal = miTurno.reduce((s, v) => s + v.total, 0);
            extraEfectivo = miTurno.filter(v => v.metodo_pago === 'Efectivo').reduce((s, v) => s + v.total, 0);
            extraTarjeta = miTurno.filter(v => v.metodo_pago === 'Tarjeta').reduce((s, v) => s + v.total, 0);
            extraTransferencia = miTurno.filter(v => v.metodo_pago === 'Transferencia').reduce((s, v) => s + v.total, 0);
        }

        let texto = `
   BOBAGUETTE - MI CORTE
==========================
Cajero: ${usuario}
Turno: ${turno}
Fecha: ${fecha}
Ventas: ${d.num_ventas + extraCount}
--------------------------
Efectivo:      $${(parseFloat(d.efectivo) + extraEfectivo).toFixed(2)}
Transferencia: $${(parseFloat(d.transferencia) + extraTransferencia).toFixed(2)}
Tarjeta:       $${(parseFloat(d.tarjeta) + extraTarjeta).toFixed(2)}
--------------------------
Gastos:       -$${parseFloat(d.gastos).toFixed(2)}
--------------------------
TOTAL NETO:    $${(parseFloat(d.total) + extraTotal).toFixed(2)}
==========================
${extraCount > 0 ? '* ' + extraCount + ' venta(s) pendiente(s) de sincronizar' : ''}
\n\n\n`;

        const win = window.open('', '_blank');
        win.document.write('<pre>' + texto + '</pre>');
        win.document.close();
        win.print();
        win.close();
    }
    </script>
</body>
</html>