// Integración del Sistema Offline-First con las vistas existentes
// Este archivo se encarga de registrar el service worker y manejar la comunicación

class OfflineIntegration {
    constructor() {
        this.isServiceWorkerReady = false;
        this.init();
    }

    async init() {
        // Registrar el service worker
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                });
                
                console.log('Service Worker registrado exitosamente:', registration.scope);
                this.isServiceWorkerReady = true;
                
                // Escuchar mensajes del service worker
                navigator.serviceWorker.addEventListener('message', (event) => {
                    this.handleServiceWorkerMessage(event);
                });
                
                // Notificar al service worker que la página está lista
                if (registration.active) {
                    registration.active.postMessage({ type: 'PAGE_READY' });
                }
                
            } catch (error) {
                console.error('Error registrando Service Worker:', error);
            }
        }
        
        // Inicializar el gestor offline
        if (window.offlineManager) {
            console.log('Offline Manager ya inicializado');
        }
    }

    handleServiceWorkerMessage(event) {
        const { type, data } = event.data;
        
        switch (type) {
            case 'SAVE_OFFLINE_OPERATION':
                this.handleSaveOfflineOperation(data);
                break;
            case 'GET_LOCAL_DATA':
                this.handleGetLocalData(data);
                break;
            case 'SYNC_LOCAL_DATA':
                this.handleSyncLocalData();
                break;
            default:
                console.log('Mensaje no manejado:', type, data);
        }
    }

    async handleSaveOfflineOperation(data) {
        // Guardar operación en IndexedDB
        if (window.offlineManager) {
            await window.offlineManager.addToPending(
                data.url, 
                data.method, 
                data.data
            );
        }
    }

    async handleGetLocalData(data) {
        // Obtener datos desde IndexedDB
        if (window.offlineManager) {
            const path = data.path;
            let storeName = '';
            
            if (path.includes('/ventas')) storeName = 'ventas';
            else if (path.includes('/corte')) storeName = 'cortes';
            else if (path.includes('/insumos')) storeName = 'insumos';
            else if (path.includes('/gastos')) storeName = 'gastos';
            
            if (storeName) {
                const result = await window.offlineManager.getAllFromLocal(storeName);
                
                // Enviar respuesta de vuelta al service worker
                if (event.ports && event.ports[0]) {
                    event.ports[0].postMessage({ data: result });
                }
            }
        }
    }

    async handleSyncLocalData() {
        // Sincronizar datos locales
        if (window.offlineManager) {
            await window.offlineManager.syncPendingOperations();
        }
    }

    // Método para mostrar el estado offline en el dashboard
    showOfflineIndicator() {
        // Crear indicador de estado en el dashboard
        const dashboard = document.querySelector('.dashboard-container') || document.body;
        
        if (!document.getElementById('offline-indicator')) {
            const indicator = document.createElement('div');
            indicator.id = 'offline-indicator';
            indicator.style.position = 'fixed';
            indicator.style.top = '10px';
            indicator.style.right = '10px';
            indicator.style.padding = '10px 20px';
            indicator.style.borderRadius = '5px';
            indicator.style.fontSize = '14px';
            indicator.style.fontWeight = 'bold';
            indicator.style.zIndex = '1000';
            indicator.style.transition = 'all 0.3s ease';
            
            dashboard.appendChild(indicator);
        }
        
        this.updateOfflineIndicator();
    }

    async updateOfflineIndicator() {
        const indicator = document.getElementById('offline-indicator');
        if (!indicator || !window.offlineManager) return;
        
        const status = await window.offlineManager.getOfflineStatus();
        
        if (status.isOnline) {
            indicator.style.backgroundColor = '#d4edda';
            indicator.style.color = '#155724';
            indicator.style.borderColor = '#c3e6cb';
            indicator.style.border = '1px solid';
            indicator.innerHTML = `
                <span style="color: #28a745; margin-right: 5px;">●</span>
                En línea
                ${status.pendingOperations > 0 ? `(${status.pendingOperations} pendientes)` : ''}
            `;
        } else {
            indicator.style.backgroundColor = '#fff3cd';
            indicator.style.color = '#856404';
            indicator.style.borderColor = '#ffeaa7';
            indicator.style.border = '1px solid';
            indicator.innerHTML = `
                <span style="color: #ffc107; margin-right: 5px;">●</span>
                Modo Offline
                ${status.pendingOperations > 0 ? `(${status.pendingOperations} pendientes)` : ''}
            `;
        }
    }

    // Método para añadir botón de sincronización manual
    addSyncButton() {
        const dashboard = document.querySelector('.dashboard-container') || document.body;
        
        if (!document.getElementById('sync-button')) {
            const syncButton = document.createElement('button');
            syncButton.id = 'sync-button';
            syncButton.innerHTML = '🔄 Sincronizar';
            syncButton.style.position = 'fixed';
            syncButton.style.bottom = '20px';
            syncButton.style.right = '20px';
            syncButton.style.padding = '15px 30px';
            syncButton.style.backgroundColor = '#007bff';
            syncButton.style.color = 'white';
            syncButton.style.border = 'none';
            syncButton.style.borderRadius = '50px';
            syncButton.style.fontSize = '16px';
            syncButton.style.cursor = 'pointer';
            syncButton.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            syncButton.style.zIndex = '1000';
            
            syncButton.addEventListener('click', async () => {
                if (window.offlineManager) {
                    await window.offlineManager.syncPendingOperations();
                    this.updateOfflineIndicator();
                }
            });
            
            dashboard.appendChild(syncButton);
        }
    }

    // Método para añadir notificación de sincronización automática
    addAutoSyncNotification() {
        // Escuchar cambios de conexión
        window.addEventListener('online', () => {
            if (window.offlineManager) {
                window.offlineManager.showStatus('Conexión restablecida. Sincronizando datos...', 'info');
                setTimeout(async () => {
                    await window.offlineManager.syncPendingOperations();
                    window.offlineManager.showStatus('Datos sincronizados exitosamente', 'success');
                    this.updateOfflineIndicator();
                }, 1000);
            }
        });
    }
}

// Inicializar la integración offline
document.addEventListener('DOMContentLoaded', () => {
    window.offlineIntegration = new OfflineIntegration();
    
    // Mostrar indicador y botón de sincronización en dashboards
    if (window.location.pathname.includes('/dashboard') || 
        window.location.pathname.includes('/corte') ||
        window.location.pathname.includes('/inventario')) {
        
        setTimeout(() => {
            window.offlineIntegration.showOfflineIndicator();
            window.offlineIntegration.addSyncButton();
            window.offlineIntegration.addAutoSyncNotification();
        }, 1000);
    }
});