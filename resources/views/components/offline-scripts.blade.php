<!-- Sistema Offline-First para Bobaguette -->
<!-- Este componente añade la funcionalidad offline sin modificar las vistas existentes -->

@if(auth()->check())
    <script>
        // Verificar si el navegador soporta las APIs necesarias
        if ('serviceWorker' in navigator && 'indexedDB' in window) {
            // Cargar los scripts offline después de que el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                // Cargar el gestor offline
                if (typeof window.offlineManager === 'undefined') {
                    // El script ya está incluido en Vite, pero aseguramos la inicialización
                    console.log('Sistema Offline-First cargado');
                }
                
                // Mostrar notificación de sistema activo
                setTimeout(() => {
                    if (window.offlineIntegration) {
                        window.offlineIntegration.showOfflineIndicator();
                        window.offlineIntegration.addSyncButton();
                    }
                }, 2000);
            });
        } else {
            console.warn('Navegador no compatible con funcionalidades offline');
        }
    </script>
    
    <!-- Indicador visual de estado offline -->
    <style>
        .offline-status-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #d4edda;
            color: #155724;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #c3e6cb;
            z-index: 1000;
            transition: all 0.3s ease;
            display: none;
        }
        
        .offline-status-badge.offline {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
            display: block;
        }
        
        .offline-status-badge.online {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
            display: block;
        }
        
        .sync-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
            transition: all 0.3s ease;
        }
        
        .sync-button.show {
            display: block;
        }
        
        .sync-button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
    </style>
    
    <div id="offline-status-badge" class="offline-status-badge">
        <span id="status-icon">●</span>
        <span id="status-text">Verificando conexión...</span>
    </div>
    
    <button id="sync-button" class="sync-button" onclick="syncOfflineData()">
        🔄 Sincronizar Datos
    </button>
    
    <script>
        function syncOfflineData() {
            if (window.offlineManager) {
                window.offlineManager.syncPendingOperations().then(() => {
                    updateOfflineStatus();
                });
            }
        }
        
        function updateOfflineStatus() {
            if (!window.offlineManager) return;
            
            window.offlineManager.getOfflineStatus().then(status => {
                const badge = document.getElementById('offline-status-badge');
                const icon = document.getElementById('status-icon');
                const text = document.getElementById('status-text');
                const syncBtn = document.getElementById('sync-button');
                
                if (status.isOnline) {
                    badge.className = 'offline-status-badge online';
                    icon.style.color = '#28a745';
                    text.textContent = 'En línea';
                    syncBtn.classList.remove('show');
                } else {
                    badge.className = 'offline-status-badge offline';
                    icon.style.color = '#ffc107';
                    text.textContent = 'Modo Offline';
                    syncBtn.classList.add('show');
                }
            });
        }
        
        // Actualizar estado cada 5 segundos
        setInterval(updateOfflineStatus, 5000);
        
        // Escuchar cambios de conexión
        window.addEventListener('online', updateOfflineStatus);
        window.addEventListener('offline', updateOfflineStatus);
    </script>
@endif