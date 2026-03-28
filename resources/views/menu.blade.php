<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#004225">
    <link rel="manifest" href="/build/manifest.webmanifest">
    <title>Bobaguette - Menú</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }
        .field { width:100%; background:#f5f4f1; border:1.5px solid transparent; border-radius:12px; padding:10px 14px; font-size:13px; font-weight:600; color:#004225; outline:none; transition:border-color 0.2s; font-family:'Instrument Sans',sans-serif; }
        .field::placeholder { color:#b0a898; font-weight:500; }
        .field:focus { border-color:#004225; background:#fff; }
        @keyframes shrink { from { width:100%; } to { width:0%; } }
        @keyframes toastBounceIn {
            0%   { opacity:0; transform:translateY(-60px) scale(0.7); }
            55%  { opacity:1; transform:translateY(10px) scale(1.04); }
            75%  { transform:translateY(-6px) scale(0.98); }
            90%  { transform:translateY(4px) scale(1.01); }
            100% { transform:translateY(0) scale(1); }
        }
        .toast-in { animation: toastBounceIn 0.6s cubic-bezier(0.34,1.56,0.64,1) forwards; }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased"
      x-data="{
        openModal: false,
        openEditModal: false,
        openLogout: false,
        openPago: false,
        openPrint: false,
        printStatus: 'idle', // idle, searching, printing, success, error
        deleteId: null,
        editData: { id: null, nombre: '', precio: '', categoria: '', imagen: '' },
        catOpen: false, catVal: '', catLabel: '',
        setCat(val, label) { this.catVal = val; this.catLabel = label; this.catOpen = false; },
        filtro: 'Todos',
        busqueda: '',
        carrito: JSON.parse(localStorage.getItem('bobaguette_carrito')) || [],
        metodoPago: 'Efectivo',
        showSuccess:  {{ session('success')  ? 'true' : 'false' }},
        showEliminar: {{ session('eliminar') ? 'true' : 'false' }},
        showWarning:  {{ session('warning')  ? 'true' : 'false' }},
        showSync: false,
        abrirEdicion(prod) {
            this.editData = { ...prod };
            this.catVal = prod.categoria;
            this.catLabel = prod.categoria;
            this.openEditModal = true;
        },
        agregarAlCarrito(id, nombre, precio, tipo = 'producto', descuento = 0) {
            const idx = this.carrito.findIndex(i => i.id === id);
            if (idx >= 0) { 
                this.carrito[idx].cantidad++; 
                // Forzar reactividad reasignando el array
                this.carrito = [...this.carrito];
            }
            else { 
                this.carrito.push({ 
                    id, 
                    nombre, 
                    precio, 
                    cantidad: 1, 
                    tipo, 
                    descuento 
                }); 
                this.carrito = [...this.carrito];
            }
            this.guardarCarrito();
        },
        quitarDelCarrito(id) {
            const idx = this.carrito.findIndex(i => i.id === id);
            if (idx >= 0) {
                this.carrito[idx].cantidad--;
                if (this.carrito[idx].cantidad <= 0) {
                    this.carrito.splice(idx, 1);
                }
                this.carrito = [...this.carrito];
            }
            this.guardarCarrito();
        },
        eliminarDelCarrito(id) { 
            this.carrito = this.carrito.filter(i => i.id !== id); 
            this.guardarCarrito();
        },
        get total() { 
            let sum = 0;
            let discountPercent = 0;
            this.carrito.forEach(i => {
                if (i.descuento > 0 && i.precio === 0) {
                    discountPercent += (i.descuento / 100) * i.cantidad;
                } else {
                    sum += i.precio * i.cantidad;
                }
            });
            return sum * (1 - discountPercent);
        },
        get totalFormato() { return '$' + Math.max(0, this.total).toFixed(2); },
        limpiarCarrito() { 
            this.carrito = []; 
            localStorage.removeItem('bobaguette_carrito');
        },
        guardarCarrito() {
            localStorage.setItem('bobaguette_carrito', JSON.stringify(this.carrito));
        },
        async submitPago(e) {
            if (!navigator.onLine) {
                e.preventDefault();
                const ventaData = {
                    total: this.total,
                    metodo_pago: this.metodoPago,
                    turno: '{{ auth()->user()->turno ?? 'Matutino' }}',
                    user_id: {{ auth()->id() }}
                };
                
                // Usamos la función global expuesta en window por resources/js/offline.js
                if (window.saveVentaOffline) {
                    const saved = await window.saveVentaOffline(ventaData);
                    if (saved) {
                        this.openPago = false;
                        this.limpiarCarrito();
                        alert('¡Venta guardada localmente! Se sincronizará automáticamente cuando vuelvas a tener internet.');
                    } else {
                        alert('Error al guardar la venta localmente.');
                    }
                } else {
                    alert('El sistema offline aún se está cargando. Por favor espera un momento.');
                }
            }
        }
      }"
      x-init="
        window.addEventListener('offline-sync-success', (e) => {
            this.showSync = true;
            setTimeout(() => { this.showSync = false; }, 4000);
            if (e.detail.action.url.includes('pago')) this.limpiarCarrito();
        });

        // Sincronizar entre pestañas/ventanas
        window.addEventListener('storage', (e) => {
            if (e.key === 'bobaguette_carrito') {
                this.carrito = JSON.parse(e.newValue) || [];
            }
        });

        window.addEventListener('close-all-modals', () => {
            this.openModal = false;
            this.openEditModal = false;
            this.openPago = false;
            this.deleteId = null;
        });

        window.addEventListener('pageshow', (event) => {
            // Re-hidratar el carrito desde localStorage al volver a la página
            const stored = localStorage.getItem('bobaguette_carrito');
            if (stored) this.carrito = JSON.parse(stored);

            if (event.persisted) {
                showSuccess = false;
                showEliminar = false;
                showWarning = false;
            }
        });

        // También al enfocar la ventana por si se cambió en otra pestaña
        window.addEventListener('focus', () => {
            const stored = localStorage.getItem('bobaguette_carrito');
            if (stored) {
                const parsed = JSON.parse(stored);
                if (JSON.stringify(parsed) !== JSON.stringify(this.carrito)) {
                    this.carrito = parsed;
                }
            }
        });

        if(showSuccess) {
            // Solo limpiar si acabamos de procesar una venta REAL
            // (evita limpiar si el usuario solo regresó con el botón 'atrás')
            localStorage.removeItem('bobaguette_carrito');
            this.carrito = [];
            setTimeout(() => showSuccess = false, 4000);
        }
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

    {{-- TOAST SINCRONIZACIÓN --}}
    <div x-show="showSync" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="toast-in fixed top-6 left-0 right-0 z-[500] flex justify-center pointer-events-none">
        <div class="relative flex items-center gap-4 bg-blue-600 text-white pl-4 pr-6 py-3 rounded-2xl shadow-[0_12px_40px_rgba(37,99,235,0.45)] border border-blue-400/30 overflow-hidden pointer-events-auto cursor-pointer" @click="showSync = false">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-70 leading-none mb-0.5">Sincronizado</p>
                <p class="text-[13px] font-bold leading-none">Venta sincronizada correctamente</p>
            </div>
            <div class="absolute bottom-0 left-0 h-[3px] w-full bg-white/20 overflow-hidden rounded-b-2xl">
                <div class="h-full bg-white/70 rounded-full" style="animation: shrink 4s linear forwards;"></div>
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <header class="bg-white border-b border-gray-100 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <div class="max-w-6xl mx-auto px-8">
            <div class="py-4 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-[28px] font-bold text-[#004225] italic tracking-tighter leading-none">B&baguette.</a>
                <div class="flex items-center gap-5">
                    {{-- Indicador Offline --}}
                    <div x-data="{ online: navigator.onLine, pendingCount: 0 }" 
                         x-init="
                            window.addEventListener('online', () => online = true); 
                            window.addEventListener('offline', () => online = false);
                            const updateCount = async () => {
                                if (window.offlineDB) {
                                    this.pendingCount = await window.offlineDB.actions.where('status').equals('pending').count();
                                }
                            };
                            updateCount();
                            setInterval(updateCount, 3000);
                         " 
                         class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-100 transition-all pointer-events-none" 
                         :class="online ? (pendingCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-green-50 text-green-600') : 'bg-red-50 text-red-600 animate-pulse'">
                        <div class="w-2 h-2 rounded-full" :class="online ? (pendingCount > 0 ? 'bg-amber-500' : 'bg-green-500') : 'bg-red-500'"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest" 
                              x-text="online ? (pendingCount > 0 ? 'Sincronizando (' + pendingCount + ')...' : 'En Línea') : 'Fuera de Línea'"></span>
                    </div>
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
    <main class="max-w-6xl mx-auto px-8 py-8">
        <div class="flex gap-6">

            {{-- IZQUIERDA: productos --}}
            <div class="flex-1">

                {{-- Buscador + botón agregar --}}
                <div class="flex gap-3 mb-5">
                    <div class="relative flex-1">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="busqueda" placeholder="Buscar producto"
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] font-medium text-[#004225] placeholder-gray-400 outline-none focus:border-[#004225] transition-colors">
                    </div>
                    @if(auth()->user()->isAdmin())
                    <button @click="openModal = true"
                            class="flex items-center gap-2 bg-[#004225] text-white px-5 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest shadow-md hover:bg-[#00311c] transition-all">
                        <span class="text-base font-black leading-none">+</span> Agregar producto
                    </button>
                    @endif
                </div>

                {{-- Filtros de categoría --}}
                <div class="flex gap-2 mb-6">
                    @foreach(['Todos', 'Promociones', 'Bebidas', 'Alimentos', 'Postres'] as $cat)
                        <button @click="filtro = '{{ $cat }}'"
                                class="px-5 py-2 rounded-xl text-[12px] font-bold transition-all"
                                :class="filtro === '{{ $cat }}' ? 'bg-[#004225] text-white shadow-md' : 'bg-white text-[#004225]/60 hover:bg-gray-50 border border-gray-100'">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>

                {{-- Grid de productos y promociones --}}
                <div class="grid grid-cols-3 gap-4">
                    {{-- Promociones --}}
                    @foreach($promociones as $promo)
                        @php
                            $precioOriginal = 0;
                            if($promo->producto1) $precioOriginal += $promo->producto1->precio;
                            if($promo->producto2) $precioOriginal += $promo->producto2->precio;
                        @endphp
                        <div x-show="(filtro === 'Todos' || filtro === 'Promociones') && (busqueda === '' || '{{ strtolower($promo->nombre) }}'.includes(busqueda.toLowerCase()))"
                             class="bg-[#004225] rounded-2xl overflow-hidden border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)] hover:shadow-md transition-all relative">
                            <div class="absolute top-2 left-2 z-10">
                                <span class="bg-amber-400 text-[#004225] text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter">PROMO</span>
                            </div>
                            {{-- Info --}}
                            <div class="p-4 pt-8">
                                <p class="text-[14px] font-bold text-white leading-tight">{{ $promo->nombre }}</p>
                                <p class="text-[10px] text-white/60 font-medium mt-0.5">{{ $promo->descripcion }}</p>
                                <div class="mt-2">
                                    @if(in_array($promo->tipo, ['precio', 'temporada']))
                                        @if($precioOriginal > 0)
                                            <p class="text-[10px] text-white/40 line-through font-bold">${{ number_format($precioOriginal, 2) }}</p>
                                        @endif
                                        <p class="text-[13px] font-black text-amber-400">Precio: ${{ number_format($promo->descuento, 2) }}</p>
                                    @else
                                        @if($precioOriginal > 0)
                                            <p class="text-[13px] font-black text-amber-400">Precio: ${{ number_format($precioOriginal * (1 - $promo->descuento/100), 2) }}</p>
                                            <p class="text-[10px] text-white/40 font-bold">Antes: ${{ number_format($precioOriginal, 2) }} ({{ $promo->descuento }}% OFF)</p>
                                        @else
                                            <p class="text-[13px] font-black text-amber-400">{{ $promo->descuento }}% OFF</p>
                                        @endif
                                    @endif
                                </div>
                                <button @click="agregarAlCarrito({{ $promo->id + 10000 }}, '{{ $promo->nombre }}', {{ in_array($promo->tipo, ['precio', 'temporada']) ? $promo->descuento : ($precioOriginal > 0 ? $precioOriginal * (1 - $promo->descuento/100) : 0) }}, '{{ $promo->tipo }}', {{ !in_array($promo->tipo, ['precio', 'temporada']) && $precioOriginal == 0 ? $promo->descuento : 0 }})"
                                        class="w-full mt-3 py-2 bg-white text-[#004225] text-[11px] font-black rounded-xl hover:bg-gray-100 active:scale-95 transition-all">
                                    + Agregar Promo
                                </button>
                            </div>
                        </div>
                    @endforeach

                    {{-- Productos --}}
                    @foreach($productos as $producto)
                        <div x-show="(filtro === 'Todos' || filtro === '{{ $producto->categoria }}') && (busqueda === '' || '{{ strtolower($producto->nombre) }}'.includes(busqueda.toLowerCase()))"
                             class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)] hover:shadow-md transition-all">
                            {{-- Imagen --}}
                            <div class="relative h-36 bg-gray-100">
                                @if($producto->imagen)
                                    <img src="{{ asset($producto->imagen) }}" alt="{{ $producto->nombre }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                {{-- Botones de acción --}}
                                <div class="absolute top-2 right-2 flex gap-1.5">
                                    @if(auth()->user()->isAdmin())
                                    <button @click="abrirEdicion({{ $producto->toJson() }})" class="w-7 h-7 bg-white/90 rounded-lg flex items-center justify-center text-gray-400 hover:text-[#004225] transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="deleteId = {{ $producto->id }}" class="w-7 h-7 bg-white/90 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            {{-- Info --}}
                            <div class="p-4">
                                <p class="text-[14px] font-bold text-[#004225] leading-tight">{{ $producto->nombre }}</p>
                                <p class="text-[10px] text-gray-400 font-medium mt-0.5">{{ $producto->categoria }}</p>
                                <p class="text-[13px] font-black text-[#e8a000] mt-1">${{ number_format($producto->precio, 2) }}</p>
                                <button @click="agregarAlCarrito({{ $producto->id }}, '{{ $producto->nombre }}', {{ $producto->precio }})"
                                        class="w-full mt-3 py-2 bg-[#004225] text-white text-[11px] font-black rounded-xl hover:bg-[#00311c] active:scale-95 transition-all">
                                    + Agregar
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- DERECHA: carrito --}}
            <div class="w-72 shrink-0">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)] sticky top-6">

                    {{-- Header carrito --}}
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                        <svg class="w-5 h-5 text-[#004225]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p class="text-[14px] font-bold text-[#004225]">Carrito</p>
                    </div>

                    {{-- Items --}}
                    <div class="px-5 py-4 space-y-3 min-h-[120px]">
                        <template x-if="carrito.length === 0">
                            <p class="text-center text-[11px] text-gray-300 font-medium italic py-6">Carrito vacío</p>
                        </template>
                        <template x-for="item in carrito" :key="item.id">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-bold text-[#004225] truncate" x-text="item.nombre"></p>
                                    <p class="text-[10px] text-gray-400 font-medium" x-text="item.precio > 0 ? '$' + (item.precio * item.cantidad).toFixed(2) : '-' + item.descuento + '% de desc.'"></p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button @click="quitarDelCarrito(item.id)"
                                            class="w-6 h-6 rounded-lg border-2 border-[#004225] text-[#004225] flex items-center justify-center font-black text-sm hover:bg-[#004225] hover:text-white transition-all">−</button>
                                    <span class="text-[13px] font-black text-[#004225] w-5 text-center" x-text="item.cantidad"></span>
                                    <button @click="agregarAlCarrito(item.id, item.nombre, item.precio)"
                                            class="w-6 h-6 rounded-lg bg-[#004225] text-white flex items-center justify-center font-black text-sm hover:bg-[#00311c] transition-all">+</button>
                                </div>
                                <button @click="eliminarDelCarrito(item.id)" class="text-gray-300 hover:text-red-400 transition-colors ml-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Total + acciones --}}
                    <div class="px-5 pb-5 border-t border-gray-100 pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <p class="text-[13px] font-bold text-[#004225]">Total:</p>
                            <p class="text-[18px] font-black text-[#e8a000]" x-text="totalFormato"></p>
                        </div>
                        <button @click="if(carrito.length > 0) openPago = true"
                                :class="carrito.length === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#00311c] active:scale-95'"
                                class="w-full py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl transition-all">
                            Procesar Pago
                        </button>
                        <button @click="limpiarCarrito()"
                                class="w-full py-2.5 border-2 border-gray-200 text-gray-400 text-[11px] font-bold rounded-xl hover:border-red-300 hover:text-red-400 transition-all">
                            Limpiar Carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- MODAL AGREGAR PRODUCTO --}}
    <div x-show="openModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openModal = false">
        <div class="bg-white rounded-[20px] w-full max-w-[480px] shadow-2xl overflow-visible" @click.stop>
            <div class="flex items-start justify-between px-7 pt-7 pb-4">
                <div>
                    <h3 class="text-[17px] font-bold text-[#004225] leading-tight">Agregar Nuevo Producto</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Completa los datos del insumo para agregarlo al inventario</p>
                </div>
                <button @click="openModal = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-xl leading-none">×</button>
            </div>
            <div class="h-px bg-gray-100 mx-7"></div>
            <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="px-7 py-5 space-y-4">
                @csrf
                <input type="hidden" name="categoria" :value="catVal">

                {{-- Nombre --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nombre del Producto</label>
                    <input type="text" name="nombre" placeholder="Ej. Café Americano" required class="field">
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Categoría</label>
                    <div class="relative">
                        <button type="button" @click="catOpen = !catOpen"
                                class="field flex items-center justify-between text-left"
                                :class="catVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                            <span x-text="catLabel || 'Selecciona o agrega una categoría'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="catOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="catOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden">
                            @foreach(['Bebidas','Alimentos','Postres'] as $opt)
                            <button type="button" @click="setCat('{{ $opt }}', '{{ $opt }}')"
                                    class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] transition-colors {{ !$loop->last ? 'border-b border-gray-50' : '' }}">{{ $opt }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Precio --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Precio</label>
                    <input type="number" name="precio" placeholder="$85" min="0" step="0.01" required class="field">
                </div>

                {{-- Imagen --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Imagen del Producto <span class="font-normal opacity-60">(Opcional)</span></label>
                    <div class="space-y-3" x-data="{ imgPreview: null, imgMode: 'file' }">
                        <div class="flex gap-2 p-1 bg-gray-100 rounded-xl">
                            <button type="button" @click="imgMode = 'file'" :class="imgMode === 'file' ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400'" class="flex-1 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all">Subir Archivo</button>
                            <button type="button" @click="imgMode = 'url'" :class="imgMode === 'url' ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400'" class="flex-1 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all">Pegar URL</button>
                        </div>

                        <div x-show="imgMode === 'file'" class="relative">
                            <input type="file" name="imagen" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                   @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => imgPreview = e.target.result; reader.readAsDataURL(file); }">
                            <div class="field flex items-center gap-3 border-dashed border-2 border-gray-200 bg-white hover:border-[#004225]/40 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span class="text-gray-400 font-medium">Seleccionar imagen...</span>
                            </div>
                        </div>

                        <div x-show="imgMode === 'url'">
                            <input type="text" name="imagen_url" placeholder="https://..." class="field" @input="imgPreview = $event.target.value">
                        </div>

                        <div x-show="imgPreview" class="relative w-full h-32 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                            <img :src="imgPreview" class="w-full h-full object-cover">
                            <button type="button" @click="imgPreview = null; $refs.fileInput.value = ''" class="absolute top-2 right-2 w-6 h-6 bg-black/50 text-white rounded-full flex items-center justify-center text-xs backdrop-blur-sm">×</button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-1 pb-2">
                    <button type="button" @click="openModal = false" class="px-6 py-3 text-[12px] font-bold text-gray-400 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit" @click="if(!catVal){ $event.preventDefault(); alert('Selecciona una categoría'); }"
                            class="flex-1 py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl hover:bg-[#00311c] active:scale-[0.98] transition-all shadow-md">Agregar Producto</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDITAR PRODUCTO --}}
    <div x-show="openEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openEditModal = false">
        <div class="bg-white rounded-[20px] w-full max-w-[480px] shadow-2xl overflow-visible" @click.stop>
            <div class="flex items-start justify-between px-7 pt-7 pb-4">
                <div>
                    <h3 class="text-[17px] font-bold text-[#004225] leading-tight">Editar Producto</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Modifica los datos del producto seleccionado</p>
                </div>
                <button @click="openEditModal = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-xl leading-none">×</button>
            </div>
            <div class="h-px bg-gray-100 mx-7"></div>
            <form :action="'{{ url('menu') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="px-7 py-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="categoria" :value="catVal">

                {{-- Nombre --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nombre del Producto</label>
                    <input type="text" name="nombre" x-model="editData.nombre" required class="field">
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Categoría</label>
                    <div class="relative">
                        <button type="button" @click="catOpen = !catOpen"
                                class="field flex items-center justify-between text-left"
                                :class="catVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                            <span x-text="catLabel || 'Selecciona una categoría'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="catOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="catOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden">
                            @foreach(['Bebidas','Alimentos','Postres'] as $opt)
                            <button type="button" @click="setCat('{{ $opt }}', '{{ $opt }}')"
                                    class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] transition-colors {{ !$loop->last ? 'border-b border-gray-50' : '' }}">{{ $opt }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Precio --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Precio</label>
                    <input type="number" name="precio" x-model="editData.precio" min="0" step="0.01" required class="field">
                </div>

                {{-- Imagen --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Imagen del Producto <span class="font-normal opacity-60">(Opcional)</span></label>
                    <div class="space-y-3" x-data="{ imgPreview: null, imgMode: 'file' }" x-init="imgPreview = editData.imagen">
                        <div class="flex gap-2 p-1 bg-gray-100 rounded-xl">
                            <button type="button" @click="imgMode = 'file'" :class="imgMode === 'file' ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400'" class="flex-1 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all">Subir Archivo</button>
                            <button type="button" @click="imgMode = 'url'" :class="imgMode === 'url' ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400'" class="flex-1 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all">Pegar URL</button>
                        </div>

                        <div x-show="imgMode === 'file'" class="relative">
                            <input type="file" name="imagen" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                   @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => imgPreview = e.target.result; reader.readAsDataURL(file); }">
                            <div class="field flex items-center gap-3 border-dashed border-2 border-gray-200 bg-white hover:border-[#004225]/40 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span class="text-gray-400 font-medium">Cambiar imagen...</span>
                            </div>
                        </div>

                        <div x-show="imgMode === 'url'">
                            <input type="text" name="imagen_url" x-model="editData.imagen" placeholder="https://..." class="field" @input="imgPreview = $event.target.value">
                        </div>

                        <div x-show="imgPreview" class="relative w-full h-32 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                            <img :src="imgPreview.startsWith('http') || imgPreview.startsWith('/') ? imgPreview : imgPreview" class="w-full h-full object-cover">
                            <button type="button" @click="imgPreview = null; editData.imagen = ''" class="absolute top-2 right-2 w-6 h-6 bg-black/50 text-white rounded-full flex items-center justify-center text-xs backdrop-blur-sm">×</button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-1 pb-2">
                    <button type="button" @click="openEditModal = false" class="px-6 py-3 text-[12px] font-bold text-gray-400 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit"
                            class="flex-1 py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl hover:bg-[#00311c] active:scale-[0.98] transition-all shadow-md">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL PROCESAR PAGO --}}
    <div x-show="openPago" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openPago = false">
        <div class="bg-white rounded-[20px] w-full max-w-sm shadow-2xl" @click.stop>
            <div class="flex items-start justify-between px-7 pt-7 pb-4">
                <div>
                    <h3 class="text-[17px] font-bold text-[#004225] leading-tight">Procesar Pago</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Selecciona el método de pago</p>
                </div>
                <button @click="openPago = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-xl leading-none">×</button>
            </div>
            <div class="h-px bg-gray-100 mx-7"></div>
            <form action="{{ route('menu.pago') }}" method="POST" class="px-7 py-5 space-y-4" @submit="submitPago($event)">
                @csrf
                <input type="hidden" name="total" :value="total">
                <input type="hidden" name="carrito" :value="JSON.stringify(carrito)">

                {{-- Resumen --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Resumen</p>
                    <template x-for="item in carrito" :key="item.id">
                        <div class="flex justify-between text-[12px] text-[#004225] font-medium py-0.5">
                            <span x-text="item.nombre + ' x' + item.cantidad"></span>
                            <span x-text="'$' + (item.precio * item.cantidad).toFixed(2)"></span>
                        </div>
                    </template>
                    <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between">
                        <p class="text-[13px] font-black text-[#004225]">Total</p>
                        <p class="text-[13px] font-black text-[#e8a000]" x-text="totalFormato"></p>
                    </div>
                </div>

                {{-- Método de pago --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-2">Método de pago</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['Efectivo', 'Tarjeta', 'Transferencia'] as $metodo)
                        <label class="cursor-pointer">
                            <input type="radio" name="metodo_pago" value="{{ $metodo }}" class="sr-only" x-model="metodoPago">
                            <div class="text-center py-2.5 rounded-xl border-2 text-[11px] font-bold transition-all"
                                 :class="metodoPago === '{{ $metodo }}' ? 'border-[#004225] bg-[#004225] text-white' : 'border-gray-200 text-gray-500 hover:border-[#004225]/40'">
                                {{ $metodo }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-1 pb-2">
                    <button type="button" @click="openPago = false" class="px-6 py-3 text-[12px] font-bold text-gray-400 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <div class="flex-1 flex gap-2">
                        <button type="button" @click="openPrint = true; printStatus = 'searching'; imprimirTicket()"
                                class="px-4 bg-amber-400 text-[#004225] text-[12px] font-black rounded-xl hover:bg-amber-500 transition-all shadow-md">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </button>
                        <button type="submit" @click="setTimeout(() => limpiarCarrito(), 500)" class="flex-1 py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl hover:bg-[#00311c] active:scale-[0.98] transition-all shadow-md">Confirmar Pago</button>
                    </div>
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
            <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Eliminar producto?</h3>
            <p class="text-[11px] text-gray-400 font-medium mb-6">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button @click="deleteId = null" class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                <form :action="'{{ url('menu') }}/' + deleteId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">Eliminar</button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL ESTADO IMPRESIÓN --}}
    <div x-show="openPrint" x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openPrint = false">
        <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
            {{-- Buscando --}}
            <template x-if="printStatus === 'searching'">
                <div class="space-y-4">
                    <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mx-auto relative overflow-hidden">
                        <div class="absolute inset-0 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
                        <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    </div>
                    <h3 class="text-[17px] font-bold italic text-[#004225]">Buscando Impresora</h3>
                    <p class="text-[11px] text-gray-400 font-medium">Por favor selecciona tu dispositivo en la ventana del navegador...</p>
                    <button @click="openPrint = false" class="w-full py-2.5 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                </div>
            </template>

            {{-- Imprimiendo --}}
            <template x-if="printStatus === 'printing'">
                <div class="space-y-4">
                    <div class="w-16 h-16 bg-[#004225]/5 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-[#004225] animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <h3 class="text-[17px] font-bold italic text-[#004225]">Imprimiendo Ticket</h3>
                    <p class="text-[11px] text-gray-400 font-medium tracking-tight">Estamos enviando los datos a tu impresora...</p>
                </div>
            </template>

            {{-- Éxito --}}
            <template x-if="printStatus === 'success'">
                <div class="space-y-4">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-[17px] font-bold italic text-[#004225]">¡Ticket Listo!</h3>
                    <p class="text-[11px] text-gray-400 font-medium">La impresión se ha completado con éxito.</p>
                    <button @click="openPrint = false" class="w-full py-3 bg-[#004225] text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-[#00311c] transition-all">Cerrar</button>
                </div>
            </template>

            {{-- Error --}}
            <template x-if="printStatus === 'error'">
                <div class="space-y-4">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-[17px] font-bold italic text-red-600">Error de Impresión</h3>
                    <p class="text-[11px] text-gray-400 font-medium">No se pudo conectar con la impresora. Intentando impresión local...</p>
                    <div class="flex gap-2">
                        <button @click="openPrint = false" class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">Cerrar</button>
                        <button @click="imprimirTicketFallback()" class="flex-1 py-3 bg-[#e8a000] text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-[#c98a00] transition-all">Imprimir Local</button>
                    </div>
                </div>
            </template>
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

    <script>
    function getTicketContent() {
        const cart = JSON.parse(localStorage.getItem('bobaguette_carrito')) || [];
        if (cart.length === 0) return null;

        const total = cart.reduce((s, i) => s + (i.precio * i.cantidad), 0);
        const fecha = new Date().toLocaleString();
        
        let texto = `
   BOBAGUETTE POS
==========================
Fecha: ${fecha}
--------------------------
`;
        cart.forEach(item => {
            const sub = (item.precio * item.cantidad).toFixed(2);
            texto += `${item.nombre.padEnd(18)} x${item.cantidad} $${sub}\n`;
        });
        
        texto += `--------------------------
TOTAL:            $${total.toFixed(2)}
==========================
   ¡Gracias por su compra!
\n\n\n`;
        return texto;
    }

    async function imprimirTicket() {
        const texto = getTicketContent();
        if (!texto) return;

        // Intentar imprimir vía Web Bluetooth
        if (navigator.bluetooth) {
            try {
                const device = await navigator.bluetooth.requestDevice({
                    filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                });
                
                // Actualizar UI a "Imprimiendo"
                const app = document.querySelector('body').__x.$data;
                app.printStatus = 'printing';

                const server = await device.gatt.connect();
                const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                
                const encoder = new TextEncoder();
                const data = encoder.encode(texto);
                await characteristic.writeValue(data);
                
                app.printStatus = 'success';
                return;
            } catch (error) {
                console.log('Bluetooth print failed or cancelled:', error);
                const app = document.querySelector('body').__x.$data;
                app.printStatus = 'error';
            }
        } else {
            const app = document.querySelector('body').__x.$data;
            app.printStatus = 'error';
        }
    }

    function imprimirTicketFallback() {
        const texto = getTicketContent();
        if (!texto) return;

        const win = window.open('', '_blank');
        win.document.write(`
            <html>
            <head>
                <title>Imprimir Ticket - Bobaguette</title>
                <style>
                    body { font-family: 'Courier New', Courier, monospace; width: 80mm; padding: 10px; background: white; }
                    pre { white-space: pre-wrap; font-size: 14px; color: #004225; }
                    .brand { text-align: center; color: #004225; font-weight: bold; font-size: 20px; margin-bottom: 10px; font-style: italic; }
                </style>
            </head>
            <body>
                <div class="brand">B&baguette.</div>
                <pre>${texto}</pre>
                <script>window.print(); window.close();<\/script>
            </body>
            </html>
        `);
        win.document.close();
        
        const app = document.querySelector('body').__x.$data;
        app.openPrint = false;
        app.printStatus = 'idle';
    }
    </script>
</body>
</html>