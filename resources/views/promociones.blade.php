<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Promociones</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    >
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Instrument Sans', sans-serif; }
        .field { width:100%; background:#f5f4f1; border:1.5px solid transparent; border-radius:12px; padding:10px 14px; font-size:13px; font-weight:600; color:#004225; outline:none; transition:border-color 0.2s; font-family:'Instrument Sans',sans-serif; }
        .field::placeholder { color:#b0a898; font-weight:500; }
        .field:focus { border-color:#004225; background:#fff; }
        .modal-overlay { background: rgba(0,66,37,0.35); backdrop-filter: blur(6px); }
        @keyframes shrink { from { width: 100%; } to { width: 0%; } }
        @keyframes toastBounceIn {
            0%   { opacity: 0; transform: translateY(-60px) scale(0.7); }
            55%  { opacity: 1; transform: translateY(10px) scale(1.04); }
            75%  { transform: translateY(-6px) scale(0.98); }
            90%  { transform: translateY(4px) scale(1.01); }
            100% { transform: translateY(0) scale(1); }
        }
        .toast-in { animation: toastBounceIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased"
      x-data="{
        openModal: false,
        openEditModal: false,
        openLogout: false,
        tipoOpen: false, tipoVal: '', tipoLabel: '',
        prod1Open: false, prod1Val: '', prod1Label: '', prod1Precio: 0,
        prod2Open: false, prod2Val: '', prod2Label: '', prod2Precio: 0,
        editData: { id: null, nombre: '', descripcion: '', descuento: '', tipo: '', activa: true, producto1_id: null, producto2_id: null },
        showSuccess:  {{ session('success')  ? 'true' : 'false' }},
        showEliminar: {{ session('eliminar') ? 'true' : 'false' }},
        showWarning:  {{ session('warning')  ? 'true' : 'false' }},
        setTipo(val, label) { this.tipoVal = val; this.tipoLabel = label; this.tipoOpen = false; },
        abrirEdicion(promo) {
            this.editData = { ...promo };
            this.tipoVal = promo.tipo;
            this.tipoLabel = this.getTipoLabel(promo.tipo);
            this.prod1Val = promo.producto1_id;
            this.prod1Label = promo.producto1 ? promo.producto1.nombre : '';
            this.prod2Val = promo.producto2_id;
            this.prod2Label = promo.producto2 ? promo.producto2.nombre : '';
            this.openEditModal = true;
        },
        getTipoLabel(val) {
            const labels = {
                'precio': 'Precio Fijo',
                'porcentaje': 'Porcentaje OFF',
                'del_dia': 'Promo del Día',
                'temporada': 'Temporada'
            };
            return labels[val] || val;
        },
        setProd1(val, label, precio = 0) { 
            this.prod1Val = val; 
            this.prod1Label = label; 
            this.prod1Precio = parseFloat(precio);
            this.prod1Open = false; 
            this.updateDescription(); 
        },
        setProd2(val, label, precio = 0) { 
            this.prod2Val = val; 
            this.prod2Label = label; 
            this.prod2Precio = parseFloat(precio);
            this.prod2Open = false; 
            this.updateDescription(); 
        },
        updateDescription() {
            let desc = '';
            if(this.prod1Label && this.prod2Label) desc = this.prod1Label + ' + ' + this.prod2Label;
            else if(this.prod1Label) desc = this.prod1Label;
            else if(this.prod2Label) desc = this.prod2Label;
            
            if(desc) document.getElementById('desc_input').value = desc;

            // Sugerir precio si es combo
             if(this.prod1Precio > 0 || this.prod2Precio > 0) {
                 let suma = this.prod1Precio + this.prod2Precio;
                 if(['precio', 'temporada'].includes(this.tipoVal)) {
                     // Si no han escrito nada en el input de descuento/precio, sugerimos la suma
                     let input = document.getElementById('descuento_input');
                     if(!input.value) input.placeholder = 'Suma: $' + suma.toFixed(2);
                 } else {
                     // Para otros tipos (del_dia, activa) reseteamos el placeholder
                     document.getElementById('descuento_input').placeholder = 'Ej. 15';
                 }
             }
        }
      }"
      x-init="
        window.addEventListener('close-all-modals', () => {
            this.openModal = false;
            this.openEditModal = false;
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
    <main class="max-w-5xl mx-auto px-8 py-10">

        {{-- Título + botón --}}
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-[26px] font-bold text-[#004225] italic tracking-tight">Promociones Activas</h2>
            <button @click="openModal = true"
                    class="flex items-center gap-2 bg-[#004225] text-white px-5 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest shadow-md hover:bg-[#00311c] hover:scale-[1.02] active:scale-95 transition-all">
                <span class="text-base font-black leading-none">+</span> Nueva Promoción
            </button>
        </div>

        {{-- Cards de promociones activas --}}
        <div class="grid grid-cols-2 gap-4 mb-8">
            @forelse($promocionesActivas as $promo)
            <div x-data="{ confirmDelete: false }" class="bg-white rounded-[20px] border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.05)] p-5 hover:shadow-[0_4px_16px_rgba(0,66,37,0.10)] transition-all">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#004225]/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10V5a2 2 0 012-2z"/></svg>
                        <p class="text-[14px] font-bold text-[#004225]">{{ $promo->nombre }}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="abrirEdicion({{ $promo->load(['producto1', 'producto2'])->toJson() }})" class="w-6 h-6 flex items-center justify-center rounded-lg text-gray-300 hover:text-[#004225] hover:bg-green-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button @click="confirmDelete = true" class="w-6 h-6 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <p class="text-[12px] text-gray-400 font-medium mb-4 leading-snug">{{ $promo->descripcion }}</p>
                <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 text-center">
                    <p class="text-[15px] font-black text-amber-600">
                        @if(in_array($promo->tipo, ['precio', 'temporada']))
                            Precio: ${{ number_format($promo->descuento, 2) }}
                        @else
                            {{ $promo->descuento }}% Descuento
                        @endif
                    </p>
                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5">Aplicado al total</p>
                </div>

                {{-- Modal confirmar eliminar --}}
                <div x-show="confirmDelete" x-cloak
                     class="fixed inset-0 z-[80] flex items-center justify-center p-4"
                     style="background: rgba(0,66,37,0.35); backdrop-filter: blur(6px);">
                    <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
                        <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Eliminar promoción?</h3>
                        <p class="text-[11px] text-gray-400 font-medium mb-6">Esta acción no se puede deshacer.</p>
                        <div class="flex gap-3">
                            <button @click="confirmDelete = false"
                                    class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">
                                Cancelar
                            </button>
                            <form action="{{ route('promociones.destroy', $promo->id) }}" method="POST" class="flex-1">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 bg-white rounded-[20px] border border-gray-100 p-10 text-center">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10V5a2 2 0 012-2z"/></svg>
                <p class="text-[13px] font-bold text-gray-400">No hay promociones activas</p>
                <p class="text-[11px] text-gray-300 mt-1">Crea una nueva promoción con el botón de arriba</p>
            </div>
            @endforelse
        </div>

        {{-- Promociones del día --}}
        <div class="bg-amber-50 border border-amber-100 rounded-[24px] p-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="text-[15px] font-bold text-[#004225]">Promociones del día</h3>
            </div>
            <div class="space-y-3">
                @forelse($promocionesDia as $promo)
                <div x-data="{ confirmDelete: false }" class="bg-white border border-gray-100 rounded-[14px] px-5 py-3.5 flex items-center justify-between shadow-[0_1px_4px_rgba(0,0,0,0.04)] hover:shadow-[0_2px_8px_rgba(0,0,0,0.07)] transition-all">
                    <div class="flex items-center gap-3">
                        @if(str_contains(strtolower($promo->nombre), 'matutino') || str_contains(strtolower($promo->nombre), 'mañana'))
                            <span class="text-base">☀️</span>
                        @elseif(str_contains(strtolower($promo->nombre), 'vespertino') || str_contains(strtolower($promo->nombre), 'tarde'))
                            <span class="text-base">🌙</span>
                        @else
                            <span class="text-base">🏷️</span>
                        @endif
                        <div>
                            <p class="text-[13px] font-bold text-[#004225]">{{ $promo->nombre }}</p>
                            <p class="text-[11px] text-gray-400 font-medium">{{ $promo->descripcion }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[12px] font-black text-amber-600 bg-amber-100 px-3 py-1 rounded-full">
                            @if(in_array($promo->tipo, ['precio', 'temporada']))
                                ${{ number_format($promo->descuento, 2) }}
                            @else
                                {{ $promo->descuento }}% off
                            @endif
                        </span>
                        <button @click="abrirEdicion({{ $promo->load(['producto1', 'producto2'])->toJson() }})" class="w-6 h-6 flex items-center justify-center rounded-lg text-gray-300 hover:text-[#004225] hover:bg-green-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button @click="confirmDelete = true" class="w-6 h-6 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>

                    {{-- Modal confirmar eliminar --}}
                    <div x-show="confirmDelete" x-cloak
                         class="fixed inset-0 z-[80] flex items-center justify-center p-4"
                         style="background: rgba(0,66,37,0.35); backdrop-filter: blur(6px);">
                        <div class="bg-white rounded-[24px] w-full max-w-xs p-8 text-center shadow-2xl" @click.stop>
                            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <h3 class="text-[17px] font-bold italic text-[#004225] mb-1">¿Eliminar promoción?</h3>
                            <p class="text-[11px] text-gray-400 font-medium mb-6">Esta acción no se puede deshacer.</p>
                            <div class="flex gap-3">
                                <button @click="confirmDelete = false"
                                        class="flex-1 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all">
                                    Cancelar
                                </button>
                                <form action="{{ route('promociones.destroy', $promo->id) }}" method="POST" class="flex-1">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white border border-gray-100 rounded-[14px] px-5 py-4 text-center">
                    <p class="text-[12px] text-gray-400 font-medium">No hay promociones del día activas</p>
                </div>
                @endforelse
            </div>
        </div>

    </main>

    {{-- MODAL CREAR PROMOCIÓN --}}
    <div x-show="openModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openModal = false">
        <div class="bg-white rounded-[20px] w-full max-w-[440px] shadow-2xl" @click.stop>
            <div class="flex items-start justify-between px-7 pt-7 pb-4">
                <div>
                    <h3 class="text-[17px] font-bold text-[#004225] leading-tight">Crear Nueva Promoción</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Completa los datos para crear una nueva promoción</p>
                </div>
                <button @click="openModal = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-xl leading-none">×</button>
            </div>
            <div class="h-px bg-gray-100 mx-7"></div>
            <form action="{{ route('promociones.store') }}" method="POST" class="px-7 py-5 space-y-4">
                @csrf
                <input type="hidden" name="tipo" :value="tipoVal">
                <input type="hidden" name="producto1_id" :value="prod1Val">
                <input type="hidden" name="producto2_id" :value="prod2Val">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Producto 1 (Opcional)</label>
                        <div class="relative">
                            <button type="button" @click="prod1Open = !prod1Open; prod2Open = false; tipoOpen = false"
                                    class="field flex items-center justify-between text-left"
                                    :class="prod1Val ? 'text-[#004225]' : 'text-[#b0a898]'">
                                <span class="truncate" x-text="prod1Label || 'Seleccionar'"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="prod1Open" x-cloak @click.away="prod1Open = false"
                                 class="absolute left-0 right-0 top-[calc(100%+6px)] z-[60] bg-white border border-gray-100 rounded-[14px] shadow-xl max-h-40 overflow-y-auto">
                                <button type="button" @click="setProd1('', '', 0); prod1Label = ''" class="w-full text-left px-4 py-2 text-[12px] hover:bg-gray-50 text-red-400 font-bold">Quitar selección</button>
                                @foreach($productos as $p)
                                <button type="button" @click="setProd1('{{ $p->id }}', '{{ $p->nombre }}', {{ $p->precio }})" class="w-full text-left px-4 py-2 text-[12px] text-[#004225] font-semibold hover:bg-[#f0f7f3] border-t border-gray-50">
                                    <div class="flex justify-between items-center">
                                        <span>{{ $p->nombre }}</span>
                                        <span class="text-[10px] opacity-60">${{ number_format($p->precio, 2) }}</span>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Producto 2 (Opcional)</label>
                        <div class="relative">
                            <button type="button" @click="prod2Open = !prod2Open; prod1Open = false; tipoOpen = false"
                                    class="field flex items-center justify-between text-left"
                                    :class="prod2Val ? 'text-[#004225]' : 'text-[#b0a898]'">
                                <span class="truncate" x-text="prod2Label || 'Seleccionar'"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="prod2Open" x-cloak @click.away="prod2Open = false"
                                 class="absolute left-0 right-0 top-[calc(100%+6px)] z-[60] bg-white border border-gray-100 rounded-[14px] shadow-xl max-h-40 overflow-y-auto">
                                <button type="button" @click="setProd2('', '', 0); prod2Label = ''" class="w-full text-left px-4 py-2 text-[12px] hover:bg-gray-50 text-red-400 font-bold">Quitar selección</button>
                                @foreach($productos as $p)
                                <button type="button" @click="setProd2('{{ $p->id }}', '{{ $p->nombre }}', {{ $p->precio }})" class="w-full text-left px-4 py-2 text-[12px] text-[#004225] font-semibold hover:bg-[#f0f7f3] border-t border-gray-50">
                                    <div class="flex justify-between items-center">
                                        <span>{{ $p->nombre }}</span>
                                        <span class="text-[10px] opacity-60">${{ number_format($p->precio, 2) }}</span>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nombre de la promoción</label>
                    <input type="text" name="nombre" placeholder="Ej. Combo desayuno" required class="field">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Descripción / Contenido</label>
                    <input type="text" name="descripcion" id="desc_input" placeholder="Ej. Baguette + Bebida" required class="field">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5"
                           x-text="['precio', 'temporada'].includes(tipoVal) ? 'Precio Final ($)' : 'Descuento (%)'"></label>
                    <input type="number" name="descuento" id="descuento_input" :placeholder="['precio', 'temporada'].includes(tipoVal) ? 'Ej. 85' : 'Ej. 15'" min="1" required class="field">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Tipo de Promoción</label>
                    <div class="relative">
                        <button type="button" @click="tipoOpen = !tipoOpen; prod1Open = false; prod2Open = false"
                                class="field flex items-center justify-between text-left"
                                :class="tipoVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                            <span x-text="tipoLabel || 'Seleccionar'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="tipoOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="tipoOpen" x-cloak @click.away="tipoOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden">
                            <button type="button" @click="setTipo('del_dia','Del día')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] border-b border-gray-50 transition-colors">Del día</button>
                            <button type="button" @click="setTipo('activa','Activa')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] border-b border-gray-50 transition-colors">Activa</button>
                            <button type="button" @click="setTipo('precio','Precio especial')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] border-b border-gray-50 transition-colors">Precio especial</button>
                            <button type="button" @click="setTipo('temporada','Platillo de Temporada')" class="w-full text-left px-5 py-3 text-[13px] font-bold text-amber-600 hover:bg-amber-50 transition-colors">Platillo de Temporada</button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-1">
                    <input type="checkbox" name="activa" id="activa" value="1" checked
                           class="w-4 h-4 rounded border-gray-300 text-[#004225] accent-[#004225]">
                    <label for="activa" class="text-[12px] font-semibold text-gray-500">Promoción activa</label>
                </div>
                <div class="flex gap-3 pt-1 pb-2">
                    <button type="button" @click="openModal = false" class="px-6 py-3 text-[12px] font-bold text-gray-400 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit"
                            @click="if(!tipoVal){ $event.preventDefault(); alert('Selecciona el tipo de promoción'); }"
                            class="flex-1 py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl hover:bg-[#00311c] active:scale-[0.98] transition-all shadow-md">
                        Agregar Promoción
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDITAR PROMOCIÓN --}}
    <div x-show="openEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openEditModal = false">
        <div class="bg-white rounded-[24px] w-full max-w-[480px] shadow-2xl overflow-visible" @click.stop>
            <div class="flex items-start justify-between px-8 pt-8 pb-4">
                <div>
                    <h3 class="text-[18px] font-bold text-[#004225] tracking-tight">Editar Promoción</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Modifica los detalles de la oferta</p>
                </div>
                <button @click="openEditModal = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-2xl leading-none">×</button>
            </div>
            
            <form :action="'{{ url('promociones') }}/' + editData.id" method="POST" class="px-8 pb-8 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="tipo" :value="tipoVal">
                <input type="hidden" name="producto1_id" :value="prod1Val">
                <input type="hidden" name="producto2_id" :value="prod2Val">

                <div class="grid grid-cols-2 gap-4">
                    {{-- Nombre --}}
                    <div class="col-span-2">
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Nombre de la promo</label>
                        <input type="text" name="nombre" x-model="editData.nombre" placeholder="Ej. Combo Parejas" required class="field">
                    </div>

                    {{-- Tipo --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Tipo</label>
                        <div class="relative">
                            <button type="button" @click="tipoOpen = !tipoOpen"
                                    class="field flex items-center justify-between text-left"
                                    :class="tipoVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                                <span x-text="tipoLabel || 'Selecciona'"></span>
                                <svg class="w-4 h-4 text-gray-400" :class="tipoOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="tipoOpen" @click.away="tipoOpen = false" class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                                @foreach(['precio' => 'Precio Fijo', 'porcentaje' => 'Porcentaje OFF', 'del_dia' => 'Promo del Día', 'temporada' => 'Temporada'] as $val => $lab)
                                <button type="button" @click="setTipo('{{ $val }}', '{{ $lab }}')" class="w-full text-left px-4 py-2.5 text-[12px] font-semibold text-[#004225] hover:bg-green-50">{{ $lab }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Valor (Precio o %) --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider" x-text="['precio', 'temporada'].includes(tipoVal) ? 'Precio Final' : 'Descuento %'"></label>
                        <input type="number" name="descuento" x-model="editData.descuento" placeholder="Ej. 15" required class="field" id="edit_descuento_input">
                    </div>
                </div>

                {{-- Productos Relacionados --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Producto 1</label>
                        <div class="relative">
                            <button type="button" @click="prod1Open = !prod1Open" class="field flex items-center justify-between text-left truncate">
                                <span class="truncate" x-text="prod1Label || 'Opcional'"></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="prod1Open" @click.away="prod1Open = false" class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                <button type="button" @click="setProd1(null, 'Ninguno')" class="w-full text-left px-4 py-2 text-[12px] font-semibold text-red-400 hover:bg-red-50 italic">Ninguno</button>
                                @foreach($productos as $p)
                                <button type="button" @click="setProd1({{ $p->id }}, '{{ $p->nombre }}', {{ $p->precio }})" class="w-full text-left px-4 py-2 text-[12px] font-semibold text-[#004225] hover:bg-green-50">{{ $p->nombre }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Producto 2</label>
                        <div class="relative">
                            <button type="button" @click="prod2Open = !prod2Open" class="field flex items-center justify-between text-left truncate">
                                <span class="truncate" x-text="prod2Label || 'Opcional'"></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="prod2Open" @click.away="prod2Open = false" class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto">
                                <button type="button" @click="setProd2(null, 'Ninguno')" class="w-full text-left px-4 py-2 text-[12px] font-semibold text-red-400 hover:bg-red-50 italic">Ninguno</button>
                                @foreach($productos as $p)
                                <button type="button" @click="setProd2({{ $p->id }}, '{{ $p->nombre }}', {{ $p->precio }})" class="w-full text-left px-4 py-2 text-[12px] font-semibold text-[#004225] hover:bg-green-50">{{ $p->nombre }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Descripción automática</label>
                    <input type="text" name="descripcion" x-model="editData.descripcion" id="edit_desc_input" required class="field bg-gray-50 border-gray-100" placeholder="Se generará al seleccionar productos">
                </div>

                {{-- Activa --}}
                <div class="flex items-center gap-3 py-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="activa" value="1" x-model="editData.activa" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#004225]"></div>
                    </label>
                    <span class="text-[12px] font-bold text-[#004225]">Promoción activa</span>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="openEditModal = false" class="flex-1 py-3.5 text-[12px] font-bold text-gray-400 rounded-2xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit" class="flex-[2] py-3.5 bg-[#004225] text-white text-[12px] font-black rounded-2xl hover:bg-[#00311c] shadow-lg active:scale-95 transition-all">Guardar Cambios</button>
                </div>
            </form>
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

</body>
</html>