import Dexie from 'dexie';

// Configuración de la base de datos local (IndexedDB)
const db = new Dexie('BobaguetteOffline');
db.version(2).stores({
    ventas: '++id, total, metodo_pago, turno, user_id, status',
    actions: '++id, url, method, data, status'
});

export const offlineDB = db;
window.offlineDB = db;

/**
 * Guarda una acción genérica para sincronización posterior
 */
export async function enqueueAction(url, method, data) {
    try {
        await db.actions.add({
            url,
            method,
            data,
            status: 'pending',
            created_at: new Date().toISOString()
        });
        console.log(`Acción [${method}] ${url} guardada localmente.`);
        return true;
    } catch (error) {
        console.error('Error al guardar acción offline:', error);
        return false;
    }
}

window.enqueueAction = enqueueAction;

// Función para guardar una venta localmente
export async function saveVentaOffline(ventaData) {
    return await enqueueAction('/menu/pago', 'POST', ventaData);
}

window.saveVentaOffline = saveVentaOffline;

// Función para obtener ventas pendientes locales
export async function getVentasPendientes() {
    try {
        const pendingActions = await db.actions
            .where('status').equals('pending')
            .toArray();

        return pendingActions
            .filter(a => a.url.includes('/menu/pago'))
            .map(a => ({
                total: parseFloat(a.data.total || 0),
                metodo_pago: a.data.metodo_pago || 'Efectivo',
                turno: a.data.turno || 'Matutino',
                pendiente: true
            }));
    } catch (e) {
        return [];
    }
}

window.getVentasPendientes = getVentasPendientes;

// Función para sincronizar todo
export async function syncAll() {
    if (!navigator.onLine) return;

    const pendingActions = await db.actions.where('status').equals('pending').toArray();

    if (pendingActions.length === 0) return;

    console.log(`Sincronizando ${pendingActions.length} acciones pendientes...`);

    for (const action of pendingActions) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            let body = action.data;
            let headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            };

            if (!(body instanceof FormData) && typeof body === 'object') {
                body = JSON.stringify(body);
                headers['Content-Type'] = 'application/json';
            }

            const response = await fetch(action.url, {
                method: action.method === 'PUT' || action.method === 'DELETE' ? 'POST' : action.method,
                headers: headers,
                body: action.method === 'PUT' || action.method === 'DELETE'
                    ? (() => {
                        const fd = new FormData();
                        for (const key in action.data) fd.append(key, action.data[key]);
                        fd.append('_method', action.method);
                        return fd;
                    })()
                    : body
            });

            if (response.ok) {
                await db.actions.update(action.id, { status: 'synced' });
                console.log(`Acción ${action.id} sincronizada.`);
                window.dispatchEvent(new CustomEvent('offline-sync-success', { detail: { action } }));
            }
        } catch (error) {
            console.error(`Error al sincronizar acción ${action.id}:`, error);
        }
    }

    // Limpiar acciones sincronizadas
    await db.actions.where('status').equals('synced').delete();
}

// Interceptar envíos de formularios globales
document.addEventListener('submit', async (e) => {
    if (!navigator.onLine) {
        const form = e.target;
        const url = form.getAttribute('action') || '';

        // EXCEPCIÓN: No interceptar el Login si estamos offline
        if (url.includes('/login')) {
            return;
        }

        const method = form.getAttribute('method')?.toUpperCase() || 'GET';

        if (method === 'POST') {
            e.preventDefault();

            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                if (!(value instanceof File)) {
                    data[key] = value;
                }
            });

            const actualMethod = data._method || 'POST';

            const saved = await enqueueAction(url, actualMethod, data);
            if (saved) {
                alert('¡Guardado fuera de línea! Se sincronizará cuando vuelvas a tener internet.');
                window.dispatchEvent(new CustomEvent('close-all-modals'));
            }
        }
    }
});

// Escuchar cambios de conexión
window.addEventListener('online', syncAll);

// Intentar sincronizar al cargar la página
if (navigator.onLine) {
    syncAll();
}