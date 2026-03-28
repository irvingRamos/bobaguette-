// Service Worker para Bobaguette - Sistema Offline-First
// Este archivo maneja el cache de recursos y la gestión de solicitudes offline

const CACHE_NAME = 'bobaguette-v1';
const urlsToCache = [
    '/',
    '/login',
    '/dashboard',
    '/menu',
    '/inventario',
    '/corte',
    '/metricas',
    '/usuarios',
    '/promociones',
    '/css/app.css',
    '/js/app.js',
    '/js/offline-manager.js',
    '/img/boba-chan.png',
    '/img/logo.png',
    '/favicon.ico'
];

// Instalación del service worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(urlsToCache);
            })
    );
});

// Activación del service worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Interceptación de solicitudes
self.addEventListener('fetch', (event) => {
    const { request } = event;
    
    // Si es una solicitud POST (operaciones de escritura)
    if (request.method === 'POST') {
        event.respondWith(
            handlePostRequest(request)
        );
        return;
    }
    
    // Si es una solicitud GET (operaciones de lectura)
    if (request.method === 'GET') {
        event.respondWith(
            handleGetRequest(request)
        );
        return;
    }
    
    // Para otras solicitudes, usar cache-first strategy
    event.respondWith(
        caches.match(request)
            .then((response) => {
                // Si encontramos el recurso en cache, lo devolvemos
                if (response) {
                    return response;
                }
                
                // Si no está en cache, intentamos la red
                return fetch(request).then(
                    (networkResponse) => {
                        // Si la solicitud tuvo éxito, la cacheamos
                        if (networkResponse && networkResponse.status === 200) {
                            const responseClone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, responseClone);
                            });
                        }
                        return networkResponse;
                    }
                ).catch(() => {
                    // Si falla la red y no está en cache, devolver una respuesta offline
                    return new Response(
                        '<h1>Modo Offline</h1><p>Estás trabajando sin conexión. Por favor, verifica tu conexión a internet.</p>',
                        {
                            status: 200,
                            headers: { 'Content-Type': 'text/html' }
                        }
                    );
                });
            })
    );
});

// Manejar solicitudes POST (operaciones de escritura)
async function handlePostRequest(request) {
    // Verificar si hay conexión
    const isOnline = await checkConnection();
    
    if (isOnline) {
        // Si hay conexión, proceder normalmente
        try {
            const response = await fetch(request);
            
            // Si la solicitud fue exitosa, verificar si es una operación que debemos sincronizar
            if (response.ok) {
                const url = new URL(request.url);
                
                // Si es una operación de sincronización, no hacer nada especial
                if (url.pathname.includes('/api/sync-')) {
                    return response;
                }
                
                // Para otras operaciones, intentar sincronizar datos locales
                try {
                    await syncLocalData();
                } catch (error) {
                    console.warn('Error sincronizando datos locales:', error);
                }
            }
            
            return response;
        } catch (error) {
            console.error('Error en solicitud POST:', error);
            throw error;
        }
    } else {
        // Si no hay conexión, guardar la operación para sincronizar después
        try {
            const requestData = await request.clone().json();
            await saveOfflineOperation(request.url, 'POST', requestData);
            
            return new Response(
                JSON.stringify({
                    success: true,
                    message: 'Operación guardada localmente. Se sincronizará al recuperar conexión.',
                    offline: true
                }),
                {
                    status: 200,
                    headers: { 'Content-Type': 'application/json' }
                }
            );
        } catch (error) {
            return new Response(
                JSON.stringify({
                    success: false,
                    message: 'Error guardando operación offline',
                    error: error.message
                }),
                {
                    status: 500,
                    headers: { 'Content-Type': 'application/json' }
                }
            );
        }
    }
}

// Manejar solicitudes GET (operaciones de lectura)
async function handleGetRequest(request) {
    const url = new URL(request.url);
    
    // Para rutas específicas, intentar desde local storage primero
    if (url.pathname.includes('/ventas') || 
        url.pathname.includes('/corte') || 
        url.pathname.includes('/insumos') ||
        url.pathname.includes('/metricas')) {
        
        try {
            // Intentar obtener datos desde IndexedDB (a través del main thread)
            const data = await getDataFromLocal(url.pathname);
            
            if (data && data.length > 0) {
                return new Response(
                    JSON.stringify({ data: data, offline: true }),
                    {
                        status: 200,
                        headers: { 'Content-Type': 'application/json' }
                    }
                );
            }
        } catch (error) {
            console.warn('Error obteniendo datos locales:', error);
        }
    }
    
    // Intentar desde cache primero
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // Si no está en cache, intentar desde la red
    try {
        const networkResponse = await fetch(request);
        
        // Si la solicitud tuvo éxito, la cacheamos
        if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
                cache.put(request, responseClone);
            });
        }
        
        return networkResponse;
    } catch (error) {
        // Si falla la red, devolver una respuesta offline para rutas específicas
        if (url.pathname.includes('/ventas') || 
            url.pathname.includes('/corte') || 
            url.pathname.includes('/insumos')) {
            
            return new Response(
                JSON.stringify({ 
                    data: [], 
                    message: 'Trabajando en modo offline',
                    offline: true 
                }),
                {
                    status: 200,
                    headers: { 'Content-Type': 'application/json' }
                }
            );
        }
        
        // Para otras rutas, devolver el HTML de offline
        return new Response(
            getOfflinePage(),
            {
                status: 200,
                headers: { 'Content-Type': 'text/html' }
            }
        );
    }
}

// Verificar conexión
async function checkConnection() {
    try {
        const response = await fetch('/api/check-connection', {
            method: 'HEAD',
            cache: 'no-cache'
        });
        return response.ok;
    } catch (error) {
        return false;
    }
}

// Guardar operación offline
async function saveOfflineOperation(url, method, data) {
    // Enviar mensaje al main thread para que guarde en IndexedDB
    self.clients.matchAll().then(clients => {
        clients.forEach(client => {
            client.postMessage({
                type: 'SAVE_OFFLINE_OPERATION',
                data: {
                    url,
                    method,
                    data,
                    timestamp: Date.now()
                }
            });
        });
    });
}

// Obtener datos desde local storage
async function getDataFromLocal(path) {
    return new Promise((resolve) => {
        self.clients.matchAll().then(clients => {
            const client = clients[0];
            if (client) {
                const messageChannel = new MessageChannel();
                
                messageChannel.port1.onmessage = (event) => {
                    resolve(event.data.data);
                };
                
                client.postMessage({
                    type: 'GET_LOCAL_DATA',
                    path: path
                }, [messageChannel.port2]);
            } else {
                resolve(null);
            }
        });
    });
}

// Sincronizar datos locales
async function syncLocalData() {
    return new Promise((resolve) => {
        self.clients.matchAll().then(clients => {
            const client = clients[0];
            if (client) {
                client.postMessage({
                    type: 'SYNC_LOCAL_DATA'
                });
            }
            resolve();
        });
    });
}

// Página offline
function getOfflinePage() {
    return `
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modo Offline - Bobaguette</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .icon {
            font-size: 60px;
            color: #ffc107;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .status {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📱</div>
        <h1>Sin Conexión</h1>
        <div class="status">Modo Offline Activado</div>
        <p>Estás trabajando sin conexión a internet. Las operaciones se guardarán localmente y se sincronizarán automáticamente cuando se recupere la conexión.</p>
        <button class="btn" onclick="window.location.reload()">Verificar Conexión</button>
    </div>
</body>
</html>
    `;
}

// Escuchar mensajes del main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});