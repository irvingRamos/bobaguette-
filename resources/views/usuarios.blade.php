<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bobaguette - Usuarios</title>
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
        
        /* Animaciones Cute */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .cute-card { 
            animation: float 6s ease-in-out infinite;
        }
        .cute-card:nth-child(2n) { animation-delay: 1s; }
        .cute-card:nth-child(3n) { animation-delay: 2s; }
    </style>
</head>
<body class="bg-[#EAE0CC] min-h-screen antialiased"
      x-data="{
        openModal: false,
        openEditModal: false,
        openLogout: false,
        deleteId: null,
        rolOpen: false,   rolVal: '',   rolLabel: '',
        turnoOpen: false, turnoVal: '', turnoLabel: '',
        editData: { id: null, name: '', email: '', rol: '', turno: '', foto: '' },
        showSuccess:  {{ session('success')  ? 'true' : 'false' }},
        showEliminar: {{ session('eliminar') ? 'true' : 'false' }},
        setRol(val, label)   { this.rolVal = val;   this.rolLabel = label;   this.rolOpen = false; },
        setTurno(val, label) { this.turnoVal = val; this.turnoLabel = label; this.turnoOpen = false; },
        abrirEdicion(user) {
            this.editData = { ...user };
            this.rolVal = user.rol;
            this.rolLabel = user.rol === 'Administrador' ? 'Jefe' : 'Trabajador';
            this.turnoVal = user.turno;
            this.turnoLabel = user.turno;
            this.password = ''; // Limpiar password al abrir edición
            this.openEditModal = true;
        },
        
        // Validación de contraseña en tiempo real
        password: '',
        get passwordCriteria() {
            return {
                length: this.password.length >= 8,
                upper: /[A-Z]/.test(this.password),
                lower: /[a-z]/.test(this.password),
                number: /[0-9]/.test(this.password),
                symbol: /[!@#$%^&*(),.?\':{}|<>]/.test(this.password)
            }
        },
        get isPasswordSecure() {
            const c = this.passwordCriteria;
            return c.length && c.upper && c.lower && c.number && c.symbol;
        }
      }"
      x-init="
        window.addEventListener('close-all-modals', () => {
            this.openModal = false;
            this.openEditModal = false;
            this.deleteId = null;
        });
        window.addEventListener('offline-sync-success', () => {
            // Recargar para ver cambios (o podrías actualizar el DOM vía JS)
            window.location.reload();
        });
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                showSuccess = false;
                showEliminar = false;
            }
        });
        if(showSuccess)  setTimeout(() => showSuccess  = false, 4000);
        if(showEliminar) setTimeout(() => showEliminar = false, 4000);
      ">

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
                        <a href="{{ $href }}" class="px-4 py-2 text-[12px] font-bold rounded-[10px] transition-all whitespace-nowrap {{ $active ? 'bg-white text-[#004225] shadow-sm' : 'text-gray-400 hover:text-[#004225]/70' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="max-w-5xl mx-auto px-8 py-10">

        {{-- Título + botón (sin bote de basura) --}}
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-[26px] font-bold text-[#004225] italic tracking-tight">Gestión de usuarios</h2>
            <button @click="openModal = true"
                    class="flex items-center gap-2 bg-[#004225] text-white px-5 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest shadow-md hover:bg-[#00311c] hover:scale-[1.02] active:scale-95 transition-all">
                <span class="text-base font-black leading-none">+</span> Nuevo usuario
            </button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-6 mb-12">
            <div class="bg-white/80 backdrop-blur-xl rounded-[32px] p-6 border border-white shadow-[0_8px_30px_rgba(0,0,0,0.02)] hover:shadow-[0_15px_35px_rgba(0,66,37,0.06)] hover:-translate-y-1 transition-all duration-500 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Total usuarios</p>
                        <p class="text-[42px] font-black text-[#004225] leading-none">{{ $totalUsuarios }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-[#004225]/5 flex items-center justify-center group-hover:bg-[#004225] group-hover:text-white transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[32px] p-6 border border-white shadow-[0_8px_30px_rgba(0,0,0,0.02)] hover:shadow-[0_15px_35px_rgba(251,191,36,0.1)] hover:-translate-y-1 transition-all duration-500 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Jefes</p>
                        <p class="text-[42px] font-black text-amber-500 leading-none">{{ $totalJefes }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-all duration-500 text-amber-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[32px] p-6 border border-white shadow-[0_8px_30px_rgba(0,0,0,0.02)] hover:shadow-[0_15px_35px_rgba(59,130,246,0.1)] hover:-translate-y-1 transition-all duration-500 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Trabajadores</p>
                        <p class="text-[42px] font-black text-blue-500 leading-none">{{ $totalTrabajadores }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-all duration-500 text-blue-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Usuarios en Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            @foreach($usuarios as $u)
            <div class="cute-card group relative bg-white/80 backdrop-blur-2xl rounded-[40px] p-8 border border-white/60 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_25px_60px_rgba(0,66,37,0.12)] transition-all duration-700 overflow-hidden">
                {{-- Círculos Decorativos Soft --}}
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-[#004225]/5 rounded-full blur-[60px] group-hover:bg-[#004225]/10 transition-colors duration-700"></div>
                <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-amber-100/30 rounded-full blur-[50px] group-hover:bg-amber-100/50 transition-colors duration-700"></div>
                
                <div class="relative flex flex-col items-center text-center">
                    {{-- Avatar con doble borde cute --}}
                    <div class="relative mb-6">
                        <div class="w-28 h-28 rounded-[36px] bg-gradient-to-tr from-[#004225]/20 via-[#004225]/5 to-amber-100/40 p-1.5 shadow-lg transform transition-transform duration-700 group-hover:rotate-6 group-hover:scale-110">
                            <div class="w-full h-full rounded-[30px] overflow-hidden border-4 border-white shadow-inner flex items-center justify-center bg-white/50 backdrop-blur-sm">
                                @if($u->foto)
                                    <img src="{{ asset($u->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-4xl font-black text-[#004225]/20 tracking-tighter">{{ substr($u->name, 0, 1) }}</span>
                                @endif
                            </div>
                        </div>
                        {{-- Badge de Rol Cute --}}
                        <div class="absolute -bottom-1 -right-1 px-4 py-1.5 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg border-2 border-white {{ $u->rol === 'Administrador' ? 'bg-amber-400 text-white' : 'bg-[#004225] text-white' }}">
                            {{ $u->rol === 'Administrador' ? 'Jefe' : 'Staff' }}
                        </div>
                    </div>

                    {{-- Info del Usuario --}}
                    <div class="mb-5">
                        <h3 class="text-[20px] font-black text-[#004225] tracking-tight mb-1 group-hover:text-green-800 transition-colors">{{ $u->name }}</h3>
                        <div class="flex items-center justify-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                            <p class="text-[12px] text-gray-400 font-bold lowercase tracking-tight">{{ $u->email }}</p>
                        </div>
                    </div>

                    {{-- Detalles con iconos cute --}}
                    <div class="flex flex-wrap justify-center gap-3 mb-8 w-full">
                        @if($u->turno)
                        <div class="px-4 py-2 rounded-2xl bg-white/50 border border-gray-100/50 shadow-sm flex items-center gap-2 group-hover:bg-white transition-colors duration-500">
                            <div class="w-5 h-5 rounded-lg bg-blue-50 flex items-center justify-center">
                                <svg class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-tighter">{{ $u->turno }}</span>
                        </div>
                        @endif
                        <div class="px-4 py-2 rounded-2xl bg-white/50 border border-gray-100/50 shadow-sm flex items-center gap-2 group-hover:bg-white transition-colors duration-500">
                            <div class="w-5 h-5 rounded-lg bg-amber-50 flex items-center justify-center">
                                <svg class="w-3 h-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-gray-500 tabular-nums tracking-tighter">{{ $u->codigo ?? 'ID-' . str_pad($u->id, 3, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>

                    {{-- Acciones con botones más cute --}}
                    <div class="flex items-center gap-3 w-full pt-6 border-t border-gray-100/80">
                        <button @click="abrirEdicion({{ $u->toJson() }})"
                                class="flex-[2.5] flex items-center justify-center gap-2.5 py-3.5 rounded-[22px] bg-[#004225] text-white hover:bg-[#00311c] hover:shadow-[0_10px_20px_rgba(0,66,37,0.2)] active:scale-95 transition-all duration-500 font-black text-[11px] uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Editar
                        </button>
                        
                        <div x-data="{ confirmDelete: false }">
                            <button @click="confirmDelete = true"
                                    class="w-[52px] h-[52px] flex items-center justify-center rounded-[22px] bg-red-50 text-red-400 hover:bg-red-500 hover:text-white hover:shadow-[0_10px_20px_rgba(239,68,68,0.2)] active:scale-90 transition-all duration-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>

                            {{-- Modal confirmar eliminar usuario --}}
                            <div x-show="confirmDelete" x-cloak
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                                 style="background: rgba(0,66,37,0.35); backdrop-filter: blur(6px);">
                                <div class="bg-white/95 backdrop-blur-2xl rounded-[44px] w-full max-w-[340px] p-10 text-center shadow-[0_30px_100px_rgba(0,0,0,0.2)] border border-white" @click.stop>
                                    <div class="w-20 h-20 bg-red-50 rounded-[30px] flex items-center justify-center mx-auto mb-8 transform -rotate-3">
                                        <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </div>
                                    <h3 class="text-[22px] font-black text-[#004225] mb-3 leading-tight tracking-tight">¿Eliminar a {{ explode(' ', $u->name)[0] }}?</h3>
                                    <p class="text-[12px] text-gray-400 font-bold mb-10 leading-relaxed px-2">Su cuenta será borrada para siempre. ¿Estás seguro de esto?</p>
                                    <div class="flex flex-col gap-3">
                                        <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" class="w-full">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-full py-4 bg-red-500 text-white text-[12px] font-black uppercase tracking-widest rounded-[22px] shadow-[0_12px_24px_rgba(239,68,68,0.3)] hover:bg-red-600 hover:shadow-[0_15px_30px_rgba(239,68,68,0.4)] active:scale-95 transition-all duration-500">
                                                Sí, eliminar ahora
                                            </button>
                                        </form>
                                        <button @click="confirmDelete = false"
                                                class="w-full py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest rounded-[22px] hover:bg-gray-50 transition-all duration-500">
                                            Mejor no, volver
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    {{-- MODAL REGISTRAR USUARIO --}}
    <div x-show="openModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openModal = false">
        <div class="bg-white rounded-[20px] w-full max-w-[440px] shadow-2xl" @click.stop>
            <div class="flex items-start justify-between px-7 pt-7 pb-4">
                <div>
                    <h3 class="text-[17px] font-bold text-[#004225] leading-tight">Registrar Nuevo Usuario</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Completa los datos para crear una nueva cuenta de usuario</p>
                </div>
                <button @click="openModal = false" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-xl leading-none">×</button>
            </div>
            <div class="h-px bg-gray-100 mx-7"></div>
            <form action="{{ route('usuarios.store') }}" method="POST" enctype="multipart/form-data" class="px-7 py-5 space-y-4">
                @csrf
                
                {{-- Errores de validación --}}
                @if($errors->any())
                    <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li class="text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <input type="hidden" name="rol"   :value="rolVal">
                <input type="hidden" name="turno" :value="turnoVal">

                {{-- FOTO DE PERFIL --}}
                <div class="flex flex-col items-center mb-4">
                    <div class="relative group" x-data="{ imgPreview: null }">
                        <div class="w-20 h-20 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden transition-all group-hover:border-[#004225]/50">
                            <template x-if="!imgPreview">
                                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </template>
                            <template x-if="imgPreview">
                                <img :src="imgPreview" class="w-full h-full object-cover">
                            </template>
                        </div>
                        <input type="file" name="foto" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer"
                               @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => imgPreview = e.target.result; reader.readAsDataURL(file); }">
                        <div class="absolute -bottom-1 -right-1 bg-[#004225] text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                            <span class="text-xs font-bold">+</span>
                        </div>
                    </div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase mt-2">Foto de perfil</p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Nombre de Usuario</label>
                    <input type="text" name="name" placeholder="Ej. Laura" required class="field">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Correo Electrónico</label>
                    <input type="email" name="email" placeholder="laura@bobaguette.com" required class="field">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Contraseña</label>
                    <input type="password" name="password" x-model="password" placeholder="Mín. 8 caracteres, Mayús, Núm y Símbolo" required class="field">
                    
                    {{-- INDICADORES DE SEGURIDAD EN TIEMPO REAL --}}
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-2 px-1">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full transition-colors" :class="passwordCriteria.length ? 'bg-green-500' : 'bg-gray-200'"></div>
                            <span class="text-[8px] font-bold uppercase tracking-tighter" :class="passwordCriteria.length ? 'text-green-600' : 'text-gray-400'">8+ caracteres</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full transition-colors" :class="passwordCriteria.upper ? 'bg-green-500' : 'bg-gray-200'"></div>
                            <span class="text-[8px] font-bold uppercase tracking-tighter" :class="passwordCriteria.upper ? 'text-green-600' : 'text-gray-400'">Mayúscula</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full transition-colors" :class="passwordCriteria.number ? 'bg-green-500' : 'bg-gray-200'"></div>
                            <span class="text-[8px] font-bold uppercase tracking-tighter" :class="passwordCriteria.number ? 'text-green-600' : 'text-gray-400'">Número</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full transition-colors" :class="passwordCriteria.symbol ? 'bg-green-500' : 'bg-gray-200'"></div>
                            <span class="text-[8px] font-bold uppercase tracking-tighter" :class="passwordCriteria.symbol ? 'text-green-600' : 'text-gray-400'">Símbolo</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" placeholder="Repite la contraseña" required minlength="6" class="field">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Rol</label>
                    <div class="relative">
                        <button type="button" @click="rolOpen = !rolOpen; turnoOpen = false"
                                class="field flex items-center justify-between text-left"
                                :class="rolVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                            <span x-text="rolLabel || 'Selecciona'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="rolOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="rolOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden">
                            <button type="button" @click="setRol('Trabajador','Trabajador')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] border-b border-gray-50 transition-colors">Trabajador</button>
                            <button type="button" @click="setRol('Administrador','Administrador')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] transition-colors">Administrador</button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5">Turno</label>
                    <div class="relative">
                        <button type="button" @click="turnoOpen = !turnoOpen; rolOpen = false"
                                class="field flex items-center justify-between text-left"
                                :class="turnoVal ? 'text-[#004225]' : 'text-[#b0a898]'">
                            <span x-text="turnoLabel || 'Seleccionar'"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="turnoOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="turnoOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-[calc(100%+6px)] z-20 bg-white border border-gray-100 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.10)] overflow-hidden">
                            <button type="button" @click="setTurno('Vespertino','Vespertino')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] border-b border-gray-50 transition-colors">Vespertino</button>
                            <button type="button" @click="setTurno('Matutino','Matutino')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] border-b border-gray-50 transition-colors">Matutino</button>
                            <button type="button" @click="setTurno('Parcial','Parcial')" class="w-full text-left px-5 py-3 text-[13px] font-semibold text-[#004225] hover:bg-[#f0f7f3] transition-colors">Parcial</button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-1 pb-2">
                    <button type="button" @click="openModal = false" class="px-6 py-3 text-[12px] font-bold text-gray-400 rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit" 
                            @click="if(!rolVal || !turnoVal){ $event.preventDefault(); alert('Selecciona rol y turno'); }"
                            :disabled="!isPasswordSecure"
                            class="flex-1 py-3 bg-[#004225] text-white text-[12px] font-black rounded-xl hover:bg-[#00311c] active:scale-[0.98] transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:grayscale">
                        Registrar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDITAR USUARIO --}}
    <div x-show="openEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay p-4"
         @keydown.escape.window="openEditModal = false">
        <div class="bg-white rounded-[24px] w-full max-w-lg shadow-2xl overflow-visible" @click.stop>
            <div class="flex items-start justify-between px-8 pt-8 pb-4">
                <div>
                    <h3 class="text-[18px] font-bold text-[#004225] tracking-tight">Editar Usuario</h3>
                    <p class="text-[11px] text-gray-400 font-medium mt-0.5">Modifica los datos del personal</p>
                </div>
                <button @click="openEditModal = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-300 hover:text-gray-500 transition-all text-2xl leading-none">×</button>
            </div>
            
            <form :action="'{{ url('usuarios') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="px-8 pb-8 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="rol" :value="rolVal">
                <input type="hidden" name="turno" :value="turnoVal">

                <div class="grid grid-cols-2 gap-4">
                    {{-- Foto --}}
                    <div class="col-span-2 flex justify-center mb-2">
                        <div class="relative group" x-data="{ imgPreview: null }">
                            <div class="w-24 h-24 rounded-3xl bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-[#004225]/30">
                                <template x-if="!imgPreview && !editData.foto">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </template>
                                <template x-if="!imgPreview && editData.foto">
                                    <img :src="'/' + editData.foto" class="w-full h-full object-cover">
                                </template>
                                <template x-if="imgPreview">
                                    <img :src="imgPreview" class="w-full h-full object-cover">
                                </template>
                            </div>
                            <input type="file" name="foto" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer"
                                   @change="const file = $event.target.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => imgPreview = e.target.result; reader.readAsDataURL(file); }">
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-[#004225] text-white rounded-xl flex items-center justify-center shadow-lg border-2 border-white pointer-events-none">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Nombre Completo</label>
                        <input type="text" name="name" x-model="editData.name" required class="field">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Correo Electrónico</label>
                        <input type="email" name="email" x-model="editData.email" required class="field">
                    </div>

                    {{-- Rol --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Rol</label>
                        <div class="relative">
                            <button type="button" @click="rolOpen = !rolOpen" class="field flex items-center justify-between text-left">
                                <span x-text="rolLabel || 'Selecciona'"></span>
                                <svg class="w-4 h-4 text-gray-400" :class="rolOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="rolOpen" @click.away="rolOpen = false" class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                                <button type="button" @click="setRol('Administrador', 'Jefe')" class="w-full text-left px-4 py-2.5 text-[12px] font-semibold text-[#004225] hover:bg-green-50">Jefe (Admin)</button>
                                <button type="button" @click="setRol('Trabajador', 'Trabajador')" class="w-full text-left px-4 py-2.5 text-[12px] font-semibold text-[#004225] hover:bg-green-50">Trabajador</button>
                            </div>
                        </div>
                    </div>

                    {{-- Turno --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Turno</label>
                        <div class="relative">
                            <button type="button" @click="turnoOpen = !turnoOpen" class="field flex items-center justify-between text-left">
                                <span x-text="turnoLabel || 'Selecciona'"></span>
                                <svg class="w-4 h-4 text-gray-400" :class="turnoOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="turnoOpen" @click.away="turnoOpen = false" class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                                @foreach(['Matutino', 'Vespertino', 'Nocturno', 'Parcial'] as $t)
                                <button type="button" @click="setTurno('{{ $t }}', '{{ $t }}')" class="w-full text-left px-4 py-2.5 text-[12px] font-semibold text-[#004225] hover:bg-green-50">{{ $t }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Password (Opcional en edición) --}}
                    <div class="col-span-2">
                        <label class="block text-[11px] font-bold text-[#004225]/70 mb-1.5 uppercase tracking-wider">Nueva Contraseña <span class="font-normal opacity-50">(dejar en blanco para mantener actual)</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="password" name="password" x-model="password" placeholder="••••••••" class="field">
                            <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" class="field">
                        </div>
                        
                        {{-- Indicadores de seguridad (solo si están escribiendo) --}}
                        <div x-show="password.length > 0" class="mt-3 grid grid-cols-5 gap-2">
                            <template x-for="(met, key) in passwordCriteria">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="h-1 w-full rounded-full transition-all duration-500" :class="met ? 'bg-green-400' : 'bg-gray-200'"></div>
                                    <span class="text-[8px] font-black uppercase tracking-tighter" :class="met ? 'text-green-600' : 'text-gray-300'" x-text="key === 'length' ? '8+ car' : (key === 'upper' ? 'Mayús' : (key === 'lower' ? 'Minús' : (key === 'number' ? 'Num' : 'Simb')))"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="openEditModal = false" class="flex-1 py-3.5 text-[12px] font-bold text-gray-400 rounded-2xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit" 
                            :disabled="password.length > 0 && !isPasswordSecure"
                            :class="password.length > 0 && !isPasswordSecure ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#00311c] active:scale-95 shadow-lg'"
                            class="flex-[2] py-3.5 bg-[#004225] text-white text-[12px] font-black rounded-2xl transition-all">Guardar Cambios</button>
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
                <form action="{{ route('logout') }}" method="POST" class="flex-1" @submit="if(!navigator.onLine) { $event.preventDefault(); window.location.href = '{{ route('login') }}'; }">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-red-500 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-600 transition-all">Salir</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>