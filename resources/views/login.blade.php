<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#004225">
    <link rel="manifest" href="/build/manifest.webmanifest">
    <title>Bobaguette - Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#E7DCC8] h-screen flex flex-col items-center justify-center antialiased font-sans overflow-hidden relative">

    <div class="absolute inset-0 flex items-center justify-center z-0 pointer-events-none">
        <img src="{{ asset('img/boba-chan.png') }}?v={{ time() }}" 
             alt="Logo Fondo" 
             class="w-[650px] h-[650px] object-contain opacity-[0.25] grayscale mix-blend-multiply mx-auto">
    </div>

    <div class="w-full max-w-[450px] flex flex-col items-center z-10 px-8" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false)">
        
        {{-- Indicador Offline en Login --}}
        <template x-if="!online">
            <div class="mb-6 px-4 py-2 bg-red-50 border border-red-200 rounded-xl text-center animate-pulse">
                <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">Modo Fuera de Línea</p>
                <p class="text-[11px] font-medium text-red-500">Se requiere conexión para iniciar sesión por seguridad.</p>
            </div>
        </template>

        {{-- Bloque de errores por si fallan las credenciales --}}
        @if($errors->any())
            <div class="mb-4 text-red-600 font-bold text-xs uppercase tracking-widest text-center">
                Credenciales incorrectas
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="w-full space-y-12">
            @csrf
            
            <div class="space-y-3 text-center">
                <label class="block text-sm font-bold text-[#004225] uppercase tracking-[0.4em]">Usuario</label>
                <input type="email" name="email" placeholder="Nombre de usuario" value="{{ old('email') }}" required
                    class="w-full py-1.5 px-8 rounded-full bg-white shadow-sm text-gray-700 text-center placeholder:text-gray-200 border-none outline-none focus:ring-1 focus:ring-[#004225]/10 transition-all">
            </div>

            <div class="space-y-3 text-center">
                <label class="block text-sm font-bold text-[#004225] uppercase tracking-[0.4em]">Contraseña</label>
                <input type="password" name="password" placeholder="Contraseña" required
                    class="w-full py-1.5 px-8 rounded-full bg-white shadow-sm text-gray-700 text-center placeholder:text-gray-200 border-none outline-none focus:ring-1 focus:ring-[#004225]/10 transition-all">
            </div>

            <button type="submit" 
                class="w-full bg-[#004225] text-white py-2.5 rounded-full font-semibold text-lg shadow-md hover:brightness-110 active:scale-[0.99] transition-all cursor-pointer">
                Ingresar
            </button>
        </form>

        <div class="mt-32 opacity-20 font-bold text-[#004225]">B&baguette.</div>
    </div>

</body>
</html>