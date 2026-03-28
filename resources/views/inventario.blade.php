<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Inventario</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        .toast-enter { animation: toastIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        .toast-leave { animation: toastOut 0.3s ease-in forwards; }
        @keyframes toastIn { from { opacity: 0; transform: translateY(-20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(-10px) scale(0.97); } }
        .insumo-row { transition: background 0.15s ease; }
        .insumo-row:hover { background: rgba(0,66,37,0.03); }
        .btn-stock { width: 28px; height: 28px; border-radius: 8px; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease; cursor: pointer; border: none; }
        .btn-stock:active { transform: scale(0.88); }
        .btn-plus  { background: #004225; color: #fff; }
        .btn-plus:hover  { background: #00301b; }
        .btn-minus { background: transparent; border: 2px solid #004225 !important; color: #004225; }
        .btn-minus:hover { background: #004225; color: #fff; }
        .bajo-minimo { color: #ef4444; animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.45; } }
        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }
        .field { width: 100%; background: #f5f4f1; border: 1.5px solid transparent; border-radius: 14px; padding: 11px 16px; font-size: 13px; font-weight: 600; color: #004225; outline: none; transition: border-color 0.2s; font-family: 'Instrument Sans', sans-serif; }
        .field::placeholder { color: #b0a898; font-weight: 500; }
        .field:focus { border-color: #004225; background: #fff; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #004225/20; border-radius: 99px; }
        .btn-delete { color: #d1d5db; transition: color 0.15s; }
        .btn-delete:hover { color: #ef4444; }
        @keyframes shrink { from { width: 100%; } to { width: 0%; } }
        @keyframes toastBounceIn { 0% { opacity: 0; transform: translateY(-60px) scale(0.7); } 55% { opacity: 1; transform: translateY(10px) scale(1.04); } 75% { transform: translateY(-6px) scale(0.98); } 90% { transform: translateY(4px) scale(1.01); } 100% { transform: translateY(0) scale(1); } }
        @keyframes toastBounceOut { 0% { opacity: 1; transform: translateY(0) scale(1); } 30% { transform: translateY(-8px) scale(1.02); } 100% { opacity: 0; transform: translateY(-40px) scale(0.9); } }
        .toast-in  { animation: toastBounceIn  0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        .toast-out { animation: toastBounceOut 0.35s ease-in forwards; }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased"
      x-data="{
        openModal: false,
        openLogout: false,
        deleteId: null,
        showSuccess:  {{ session('success')  ? 'true' : 'false' }},
        showEliminar: {{ session('eliminar') ? 'true' : 'false' }},
        showWarning:  {{ session('warning')  ? 'true' : 'false' }},
      }"
      x-init="
        window.addEventListener('close-all-modals', () => {
            this.openModal = false;
            this.deleteId = null;
        });
        window.addEventListener('offline-sync-success', () => {
            window.location.reload();
        });
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                showSuccess = false;
                showEliminar = false;
                showWarning = false;
            }
        });
        if(showSuccess)  setTimeout(() => showSuccess  = false, 4000);
        if(showEliminar) setTimeout(() => showEliminar = false, 4000);
        if(showWarning)  setTimeout(() => showWarning  = false, 5000);
      ">

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

    {{-- TOAST ÉXITO --}}
    @if(session('success'))
    <div x-show="showSuccess" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="toast-in fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
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

    {{-- TOAST ELIMINAR --}}
    @if(session('eliminar'))
    <div x-show="showEliminar" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="toast-in fixed top-6 left-0 right-0 z-[300] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-[#1a0505] text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(180,0,0,0.45)] border border-red-900/40 overflow-hidden">
            <div class="absolute inset-0 bg-red-600/15 blur-xl pointer-events-none"></div>
            <div class="relative w-8 h-8 rounded-xl bg-red-500/30 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
    @endif

    {{-- HEADER --}}
    <header class="bg-white border-b border-gray-100 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <div class="max-w-5xl mx-auto px-8">
            <div class="py-4 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-[28px] font-bold text-[#004225] italic tracking-tighter leading-none">
                    B&baguette.
                </a>
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

            {{-- NAV — único cambio: detección dinámica de ruta activa --}}
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
    <main class="max-w-5xl mx-auto px-8 py-10">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h2 class="text-[28px] font-bold text-[#004225] italic tracking-tight leading-tight">Inventario de Insumos</h2>
                <p class="text-[12px] font-semibold text-[#004225]/50 mt-0.5">Control de stock e insumos</p>
            </div>
            @if(auth()->user()->isAdmin())
            <button @click="openModal = true"
                    class="flex items-center gap-2 bg-[#004225] text-white px-6 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest shadow-md hover:bg-[#00311c] hover:scale-[1.02] active:scale-95 transition-all">
                <span class="text-base font-black leading-none">+</span> Agregar Insumo
            </button>
            @endif
        </div>

        <div class="space-y-10">
            @php 
                $defaultCats = ['Bebidas', 'Alimentos', 'Postres'];
                $existingCats = $insumos->pluck('categoria')->unique()->toArray();
                $categories = array_unique(array_merge($defaultCats, $existingCats));
                sort($categories);
            @endphp
            @foreach($categories as $cat)
                @php $items = $insumos->where('categoria', $cat); @endphp
                <section>
                    <p class="text-center text-[10px] font-black tracking-[0.45em] text-[#004225]/70 uppercase mb-3">{{ $cat }}</p>
                    <div class="bg-white rounded-[28px] shadow-[0_2px_12px_rgba(0,0,0,0.06)] overflow-hidden border border-gray-100/80">
                        @forelse($items as $insumo)
                            <div class="insumo-row flex justify-between items-center px-10 py-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div>
                                    <p class="text-[15px] font-bold italic text-[#004225] leading-tight">{{ $insumo->nombre }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">Mín. {{ $insumo->nivel_minimo }} {{ $insumo->unidad }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-baseline gap-1 min-w-[44px] justify-end">
                                        <span class="text-[22px] font-black leading-none {{ $insumo->cantidad <= $insumo->nivel_minimo ? 'bajo-minimo' : 'text-[#004225]' }}">{{ $insumo->cantidad }}</span>
                                        <span class="text-[9px] font-bold text-gray-300 uppercase">{{ $insumo->unidad }}</span>
                                    </div>
                                    
                                    @if(auth()->user()->isAdmin())
                                    <form action="{{ route('inventario.updateStock', $insumo->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="restar">
                                        <button type="submit" class="btn-stock btn-minus">−</button>
                                    </form>
                                    <form action="{{ route('inventario.updateStock', $insumo->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="sumar">
                                        <button type="submit" class="btn-stock btn-plus">+</button>
                                    </form>
                                    <button @click="deleteId = {{ $insumo->id }}" class="btn-delete w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-14 text-center text-[9px] font-black italic tracking-[0.55em] text-gray-300 uppercase">Sin registros</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </main>

    {{-- MODAL AGREGAR INSUMO --}}
    <div x-show="openModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="bg-white rounded-[20px] w-full max-w-[480px] shadow-2xl overflow-visible"
             x-data="{ catOpen: false, catVal: '', catLabel: '', uniOpen: false, uniVal: '', uniLabel: '', showNewCat: false, newCatVal: '',
                        setCat(val, label) { 
                            if(val === '__new__') {
                                this.showNewCat = true;
                                this.catVal = '';
                                this.catLabel = 'Nueva Categoría...';
                                setTimeout(() => { this.$refs.newCatInput.focus(); }, 100);
                            } else {
                                this.showNewCat = false;
                                this.catVal = val; 
                                this.catLabel = label; 
                            }
                            this.catOpen = false; 
                        },
                        setUni(val, label) { this.uniVal = val; this.uniLabel = label; this.uniOpen = false; } }"
             @click.stop @click.away="catOpen = false; uniOpen = false">

            <div class="flex items-start justify-between px-7 pt-7 pb-4">
                <div>
                    <h3 class="text-[17px] font-bold text-[#004225] leading-tight">Agregar Nuevo Insumo</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Completa los datos del insumo para agregarlo al inventario</p>
                </div>
                <button @click="openModal = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-xl leading-none mt-0.5">×</button>
            </div>
            <div class="h-px bg-gray-100 mx-7"></div>

            <form action="{{ route('inventario.store') }}" method="POST" class="px-7 py-5 space-y-4">
                @csrf
                <input type="hidden" name="categoria" :value="showNewCat ? newCatVal : catVal">
                <input type="hidden" name="unidad"    :value="uniVal">

                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nombre del insumo</label>
                    <input type="text" name="nombre" placeholder="Ej. Café" required class="field w-full" autocomplete="off">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Categoría</label>
                    <div class="relative">
                        <button type="button" @click="catOpen = !catOpen; uniOpen = false"
                                class="field w-full flex items-center justify-between text-left"
                                :class="(catVal || showNewCat) ? 'text-[#004225]' : 'text-[#b0a898]'">
                            <span x-text="catLabel || 'Selecciona o agrega una categoría'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="catOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="catOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                             class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden max-h-48 overflow-y-auto">
                            @php 
                                $defaultCats = ['Bebidas','Alimentos','Postres'];
                                $existingCats = $insumos->pluck('categoria')->unique()->toArray();
                                $allCats = array_unique(array_merge($defaultCats, $existingCats));
                                sort($allCats);
                            @endphp
                            @foreach($allCats as $opt)
                            <button type="button" @click="setCat('{{ $opt }}', '{{ $opt }}')"
                                    class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] transition-colors {{ !$loop->last ? 'border-b border-gray-50' : '' }}"
                                    :class="catVal === '{{ $opt }}' ? 'bg-[#f0f7f3]' : ''">{{ $opt }}</button>
                            @endforeach
                            <button type="button" @click="setCat('__new__', 'Nueva Categoría...')"
                                    class="w-full text-left px-5 py-3 text-[13px] font-bold text-[#004225] hover:bg-[#f0f7f3] transition-colors border-t border-gray-100 bg-gray-50/50">
                                <span class="text-[#004225] opacity-50 mr-1">+</span> Agregar nueva...
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="showNewCat" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-3">
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nombre de la nueva categoría</label>
                    <input type="text" x-model="newCatVal" x-ref="newCatInput" placeholder="Ej. Panadería" class="field w-full" :required="showNewCat">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Cantidad Inicial</label>
                        <input type="number" name="cantidad" placeholder="20" min="0" required class="field w-full">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Unidad</label>
                        <div class="relative">
                            <button type="button" @click="uniOpen = !uniOpen; catOpen = false"
                                    class="field w-full flex items-center justify-between text-left"
                                    :class="uniVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                                <span x-text="uniLabel || 'Seleccionar'"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="uniOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="uniOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                                 class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden">
                                @foreach(['Litros'=>'L','Piezas'=>'pzas','Kilos'=>'kg'] as $label => $val)
                                <button type="button" @click="setUni('{{ $val }}', '{{ $label }}')"
                                        class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] transition-colors {{ !$loop->last ? 'border-b border-gray-50' : '' }}"
                                        :class="uniVal === '{{ $val }}' ? 'bg-[#f0f7f3]' : ''">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nivel Mínimo</label>
                    <input type="number" name="nivel_minimo" placeholder="10" min="0" required class="field w-full">
                    <p class="text-[10px] text-gray-400 font-medium mt-2">Se alertará cuando el stock esté por debajo de este nivel</p>
                </div>

                <div class="flex gap-3 pt-1 pb-2">
                    <button type="button" @click="openModal = false" class="px-6 py-3 text-[12px] font-bold text-gray-400 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit" @click="let finalCat = showNewCat ? newCatVal : catVal; if(!finalCat || !uniVal) { $event.preventDefault(); alert('Selecciona categoría y unidad'); }"
                            class="flex-1 py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl hover:bg-[#00311c] active:scale-[0.98] transition-all shadow-md">Agregar Insumo</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL CONFIRMACIÓN ELIMINAR --}}
    <div x-show="deleteId" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="deleteId = null">
        <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Eliminar insumo?</h3>
            <p class="text-[11px] text-gray-400 font-medium mb-6">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button @click="deleteId = null" class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                <form :action="'{{ url('inventario') }}/' + deleteId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">Eliminar</button>
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
                <form action="{{ route('logout') }}" method="POST" class="flex-1" @submit="if(!navigator.onLine) { $event.preventDefault(); window.location.href = '{{ route('login') }}'; }">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">Salir</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>