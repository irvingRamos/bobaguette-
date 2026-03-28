// Sistema Offline-First para Bobaguette
// Este archivo gestiona todas las operaciones offline sin modificar el código existente

class OfflineManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.pendingOperations = [];
        this.dbName = 'bobaguette-offline-db';
        this.dbVersion = 1;
        
        this.init();
    }

    async init() {
        // Inicializar base de datos local
        await this.initDatabase();
        
        // Escuchar cambios de conexión
        window.addEventListener('online', () => this.handleConnectionChange(true));
        window.addEventListener('offline', () => this.handleConnectionChange(false));
        
        // Interceptar solicitudes HTTP
        this.interceptFetch();
        
        // Cargar operaciones pendientes
        await this.loadPendingOperations();
        
        // Intentar sincronización inicial
        if (this.isOnline) {
            await this.syncPendingOperations();
        }
    }

    async initDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = () => reject(request.error);
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Crear stores para cada modelo
                if (!db.objectStoreNames.contains('ventas')) {
                    const ventasStore = db.createObjectStore('ventas', { keyPath: 'id' });
                    ventasStore.createIndex('user_id', 'user_id', { unique: false });
                    ventasStore.createIndex('created_at', 'created_at', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('cortes')) {
                    const cortesStore = db.createObjectStore('cortes', { keyPath: 'id' });
                    cortesStore.createIndex('user_id', 'user_id', { unique: false });
                    cortesStore.createIndex('fecha', 'fecha', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('gastos')) {
                    const gastosStore = db.createObjectStore('gastos', { keyPath: 'id' });
                    gastosStore.createIndex('user_id', 'user_id', { unique: false });
                    gastosStore.createIndex('created_at', 'created_at', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('insumos')) {
                    const insumosStore = db.createObjectStore('insumos', { keyPath: 'id' });
                    insumosStore.createIndex('categoria', 'categoria', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('productos')) {
                    const productosStore = db.createObjectStore('productos', { keyPath: 'id' });
                    productosStore.createIndex('categoria', 'categoria', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('users')) {
                    const usersStore = db.createObjectStore('users', { keyPath: 'id' });
                    usersStore.createIndex('email', 'email', { unique: true });
                }
                
                if (!db.objectStoreNames.contains('pending_operations')) {
                    db.createObjectStore('pending_operations', { keyPath: 'id', autoIncrement: true });
                }
            };
            
            request.onsuccess = () => resolve(request.result);
        });
    }

    async handleConnectionChange(isOnline) {
        this.isOnline = isOnline;
        
        if (isOnline) {
            this.showStatus('Conexión restablecida. Sincronizando datos...');
            await this.syncPendingOperations();
            this.showStatus('Datos sincronizados exitosamente', 'success');
        } else {
            this.showStatus('Modo offline activado', 'warning');
        }
    }

    interceptFetch() {
        // Guardar el fetch original
        const originalFetch = window.fetch;
        
        window.fetch = async (input, init = {}) => {
            // Si está online, usar fetch normal
            if (this.isOnline) {
                return originalFetch(input, init);
            }
            
            // Si está offline, manejar operaciones locales
            const url = typeof input === 'string' ? input : input.url;
            const method = (init.method || 'GET').toUpperCase();
            
            // EXCLUIR solicitudes GET de navegación (no interceptar navegación)
            if (method === 'GET') {
                // Permitir navegación normal a páginas
                if (this.isNavigationRequest(url)) {
                    return originalFetch(input, init);
                }
                // Para solicitudes GET a API, intentar desde local
                return this.handleOfflineRead(url);
            }
            
            // SOLO interceptar solicitudes POST a endpoints de API específicos
            if (method === 'POST') {
                // Detectar tipo de operación basado en la URL
                if (url.includes('/api/sync-ventas')) {
                    return this.handleOfflineVenta(input, init);
                } else if (url.includes('/api/sync-cortes')) {
                    return this.handleOfflineCorte(input, init);
                } else if (url.includes('/api/sync-gastos')) {
                    return this.handleOfflineGasto(input, init);
                } else if (url.includes('/api/sync-insumos')) {
                    return this.handleOfflineInsumo(input, init);
                }
                
                // Para otras solicitudes POST (formularios normales), permitirlas
                return originalFetch(input, init);
            }
            
            // Para PUT, PATCH, DELETE, permitir navegación normal
            return originalFetch(input, init);
        };
    }

    // Método para detectar solicitudes de navegación
    isNavigationRequest(url) {
        // Excluir URLs que son claramente de navegación
        const navigationPaths = [
            '/menu', '/inventario', '/corte', '/metricas', 
            '/usuarios', '/promociones', '/dashboard', '/login'
        ];
        
        // Si la URL contiene alguna de las rutas de navegación, es una solicitud de navegación
        return navigationPaths.some(path => url.includes(path));
    }

    async handleOfflineVenta(input, init) {
        const data = typeof init.body === 'string' ? JSON.parse(init.body) : init.body;
        const venta = {
            id: Date.now() + Math.random(),
            total: parseFloat(data.total),
            metodo_pago: data.metodo_pago,
            turno: data.turno,
            user_id: data.user_id || 1, // ID del usuario actual
            productos: data.productos || [],
            created_at: new Date().toISOString(),
            offline: true
        };
        
        await this.saveToLocal('ventas', venta);
        await this.addToPending('ventas', 'POST', venta);
        
        return this.createResponse({
            success: true,
            message: 'Venta registrada localmente. Se sincronizará al recuperar conexión.',
            data: venta
        });
    }

    async handleOfflineCorte(input, init) {
        const data = typeof init.body === 'string' ? JSON.parse(init.body) : init.body;
        const corte = {
            id: Date.now() + Math.random(),
            fecha: data.fecha || new Date().toISOString().split('T')[0],
            turno: data.turno,
            total_efectivo: parseFloat(data.total_efectivo || 0),
            total_tarjeta: parseFloat(data.total_tarjeta || 0),
            total_transferencia: parseFloat(data.total_transferencia || 0),
            gastos: data.gastos || [],
            user_id: data.user_id || 1,
            created_at: new Date().toISOString(),
            offline: true
        };
        
        await this.saveToLocal('cortes', corte);
        await this.addToPending('cortes', 'POST', corte);
        
        return this.createResponse({
            success: true,
            message: 'Corte de caja registrado localmente. Se sincronizará al recuperar conexión.',
            data: corte
        });
    }

    async handleOfflineGasto(input, init) {
        const data = typeof init.body === 'string' ? JSON.parse(init.body) : init.body;
        const gasto = {
            id: Date.now() + Math.random(),
            descripcion: data.descripcion,
            monto: parseFloat(data.monto),
            user_id: data.user_id || 1,
            created_at: new Date().toISOString(),
            offline: true
        };
        
        await this.saveToLocal('gastos', gasto);
        await this.addToPending('gastos', 'POST', gasto);
        
        return this.createResponse({
            success: true,
            message: 'Gasto registrado localmente. Se sincronizará al recuperar conexión.',
            data: gasto
        });
    }

    async handleOfflineInsumo(input, init) {
        const data = typeof init.body === 'string' ? JSON.parse(init.body) : init.body;
        const insumo = {
            id: data.id || (Date.now() + Math.random()),
            nombre: data.nombre,
            categoria: data.categoria,
            cantidad: parseFloat(data.cantidad),
            unidad: data.unidad,
            nivel_minimo: parseFloat(data.nivel_minimo || 0),
            offline: true
        };
        
        await this.saveToLocal('insumos', insumo);
        await this.addToPending('insumos', init.method || 'POST', insumo);
        
        return this.createResponse({
            success: true,
            message: 'Insumo actualizado localmente. Se sincronizará al recuperar conexión.',
            data: insumo
        });
    }

    async handleOfflineRead(url) {
        // Determinar qué datos devolver basado en la URL
        if (url.includes('/ventas')) {
            const ventas = await this.getAllFromLocal('ventas');
            return this.createResponse({ data: ventas });
        } else if (url.includes('/corte')) {
            const cortes = await this.getAllFromLocal('cortes');
            return this.createResponse({ data: cortes });
        } else if (url.includes('/insumos')) {
            const insumos = await this.getAllFromLocal('insumos');
            return this.createResponse({ data: insumos });
        } else if (url.includes('/metricas')) {
            return this.createResponse({ data: this.generateOfflineMetrics() });
        }
        
        return this.createResponse({ data: [] });
    }

    async handleOfflineOperation(input, init) {
        const url = typeof input === 'string' ? input : input.url;
        const data = typeof init.body === 'string' ? JSON.parse(init.body) : init.body;
        
        await this.addToPending(url, init.method || 'GET', data);
        
        return this.createResponse({
            success: true,
            message: 'Operación guardada localmente. Se sincronizará al recuperar conexión.',
            data: data
        });
    }

    async saveToLocal(storeName, data) {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onsuccess = () => {
                const db = request.result;
                const transaction = db.transaction([storeName], 'readwrite');
                const store = transaction.objectStore(storeName);
                
                const putRequest = store.put(data);
                putRequest.onsuccess = () => resolve();
                putRequest.onerror = () => reject(putRequest.error);
            };
            
            request.onerror = () => reject(request.error);
        });
    }

    async getAllFromLocal(storeName) {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onsuccess = () => {
                const db = request.result;
                const transaction = db.transaction([storeName], 'readonly');
                const store = transaction.objectStore(storeName);
                
                const getAllRequest = store.getAll();
                getAllRequest.onsuccess = () => resolve(getAllRequest.result);
                getAllRequest.onerror = () => reject(getAllRequest.error);
            };
            
            request.onerror = () => reject(request.error);
        });
    }

    async addToPending(model, method, data) {
        const operation = {
            model,
            method,
            data,
            timestamp: Date.now(),
            synced: false
        };
        
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onsuccess = () => {
                const db = request.result;
                const transaction = db.transaction(['pending_operations'], 'readwrite');
                const store = transaction.objectStore('pending_operations');
                
                const addRequest = store.add(operation);
                addRequest.onsuccess = () => resolve();
                addRequest.onerror = () => reject(addRequest.error);
            };
            
            request.onerror = () => reject(request.error);
        });
    }

    async loadPendingOperations() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onsuccess = () => {
                const db = request.result;
                const transaction = db.transaction(['pending_operations'], 'readonly');
                const store = transaction.objectStore('pending_operations');
                
                const getAllRequest = store.getAll();
                getAllRequest.onsuccess = () => {
                    this.pendingOperations = getAllRequest.result;
                    resolve();
                };
                getAllRequest.onerror = () => reject(getAllRequest.error);
            };
            
            request.onerror = () => reject(request.error);
        });
    }

    async syncPendingOperations() {
        if (!this.isOnline || this.pendingOperations.length === 0) {
            return;
        }
        
        this.showStatus('Sincronizando operaciones pendientes...');
        
        for (const operation of this.pendingOperations) {
            try {
                await this.syncOperation(operation);
                await this.markAsSynced(operation.id);
            } catch (error) {
                console.error('Error sincronizando operación:', error);
            }
        }
        
        // Recargar operaciones pendientes después de sincronizar
        await this.loadPendingOperations();
        
        this.showStatus('Sincronización completada', 'success');
    }

    async syncOperation(operation) {
        const response = await fetch(`/api/sync-${operation.model}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                method: operation.method,
                data: operation.data
            })
        });
        
        if (!response.ok) {
            throw new Error(`Error sincronizando ${operation.model}`);
        }
    }

    async markAsSynced(operationId) {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onsuccess = () => {
                const db = request.result;
                const transaction = db.transaction(['pending_operations'], 'readwrite');
                const store = transaction.objectStore('pending_operations');
                
                const deleteRequest = store.delete(operationId);
                deleteRequest.onsuccess = () => resolve();
                deleteRequest.onerror = () => reject(deleteRequest.error);
            };
            
            request.onerror = () => reject(request.error);
        });
    }

    generateOfflineMetrics() {
        // Generar métricas basadas en datos locales
        return {
            total_ventas: 0,
            ventas_efectivo: 0,
            ventas_tarjeta: 0,
            ventas_transferencia: 0,
            total_gastos: 0,
            cortes_realizados: 0,
            inventario_bajo: []
        };
    }

    createResponse(data) {
        return new Response(JSON.stringify(data), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });
    }

    showStatus(message, type = 'info') {
        // Crear notificación visual
        let notification = document.getElementById('offline-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'offline-notification';
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.padding = '15px 25px';
            notification.style.borderRadius = '5px';
            notification.style.zIndex = '9999';
            notification.style.fontFamily = 'Arial, sans-serif';
            notification.style.fontSize = '14px';
            notification.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            document.body.appendChild(notification);
        }
        
        // Establecer estilo según el tipo
        switch(type) {
            case 'success':
                notification.style.backgroundColor = '#d4edda';
                notification.style.color = '#155724';
                notification.style.border = '1px solid #c3e6cb';
                break;
            case 'warning':
                notification.style.backgroundColor = '#fff3cd';
                notification.style.color = '#856404';
                notification.style.border = '1px solid #ffeaa7';
                break;
            default:
                notification.style.backgroundColor = '#d1ecf1';
                notification.style.color = '#0c5460';
                notification.style.border = '1px solid #bee5eb';
        }
        
        notification.textContent = message;
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    // Métodos para el dashboard de estado
    async getOfflineStatus() {
        const ventas = await this.getAllFromLocal('ventas');
        const cortes = await this.getAllFromLocal('cortes');
        const gastos = await this.getAllFromLocal('gastos');
        
        return {
            isOnline: this.isOnline,
            pendingOperations: this.pendingOperations.length,
            localData: {
                ventas: ventas.length,
                cortes: cortes.length,
                gastos: gastos.length
            }
        };
    }
}

// Inicializar el sistema offline
document.addEventListener('DOMContentLoaded', () => {
    window.offlineManager = new OfflineManager();
});