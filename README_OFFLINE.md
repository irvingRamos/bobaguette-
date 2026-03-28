# Sistema Offline-First para Bobaguette

Este sistema permite que tu punto de venta funcione completamente sin conexión a internet, sincronizando los datos automáticamente cuando se recupere la conexión.

## Características

✅ **Funcionamiento Completo Offline**: Todas las operaciones (ventas, cortes de caja, inventario) funcionan sin conexión  
✅ **Sincronización Automática**: Los datos se sincronizan automáticamente al recuperar conexión  
✅ **Indicadores de Estado**: Visualización clara del estado de conexión y operaciones pendientes  
✅ **Sin Modificaciones al Código Existente**: El sistema se integra sin tocar tus funciones actuales  
✅ **Cache Completo**: Todos los recursos se cachean para funcionar offline  
✅ **Gestión de Turnos**: Funciona con múltiples usuarios y turnos offline  

## Archivos Creados

### Archivos JavaScript
- `resources/js/offline-manager.js` - Gestor principal de operaciones offline
- `resources/js/offline-integration.js` - Integración con el service worker
- `public/sw.js` - Service worker para cache y gestión de solicitudes

### Archivos PHP
- `routes/web.php` - Nuevas rutas para sincronización (líneas añadidas)
- `app/Http/Controllers/CorteCajaController.php` - Métodos de sincronización para ventas, cortes y gastos
- `app/Http/Controllers/InventarioController.php` - Método de sincronización para insumos

### Archivos de Configuración
- `vite.config.js` - Configuración actualizada para incluir los nuevos scripts
- `resources/views/components/offline-scripts.blade.php` - Componente para mostrar indicadores

## Instalación

1. **Compilar los assets**:
   ```bash
   npm run build
   ```

2. **Actualizar la caché de Vite**:
   ```bash
   php artisan vite:clear
   ```

3. **Limpiar caché de Laravel**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

4. **Recargar el navegador** para que se registre el service worker

## Uso

### Modo Online
- El sistema funciona normalmente como antes
- Todas las operaciones se guardan en la base de datos principal
- Indicador verde mostrando "En línea"

### Modo Offline
- Cuando se pierde la conexión, el sistema detecta automáticamente el cambio
- Indicador amarillo mostrando "Modo Offline"
- Todas las operaciones se guardan localmente en IndexedDB
- Se muestra un botón de sincronización manual

### Sincronización
- **Automática**: Cuando se recupera la conexión, los datos se sincronizan automáticamente
- **Manual**: Puedes forzar la sincronización con el botón "🔄 Sincronizar Datos"
- **Estado**: Puedes ver cuántas operaciones están pendientes de sincronización

## Operaciones Soportadas Offline

### Ventas
- Registro de ventas con diferentes métodos de pago
- Selección de turnos
- Asociación con usuarios

### Cortes de Caja
- Registro de cortes con desglose por métodos de pago
- Asociación de gastos al corte
- Control por turnos

### Gastos
- Registro de gastos con descripción
- Asociación con turnos y usuarios
- Posibilidad de aumentar inventario al registrar gasto

### Inventario
- Consulta de inventario local
- Actualización de cantidades
- Control de niveles mínimos

## Indicadores Visuales

### Badge de Estado
- **Verde**: Sistema en línea
- **Amarillo**: Sistema en modo offline
- **Número entre paréntesis**: Operaciones pendientes de sincronización

### Botón de Sincronización
- Aparece solo en modo offline
- Permite sincronizar manualmente los datos
- Se oculta automáticamente al recuperar conexión

## Compatibilidad

### Navegadores Soportados
- Chrome 60+
- Firefox 55+
- Safari 11.1+
- Edge 79+

### Requisitos del Sistema
- IndexedDB (para almacenamiento local)
- Service Workers (para cache offline)
- Fetch API (para interceptación de solicitudes)

## Solución de Problemas

### El sistema no funciona offline
1. Verifica que el service worker se haya registrado (F12 > Application > Service Workers)
2. Asegúrate de que el navegador soporte IndexedDB
3. Revisa la consola del navegador para errores

### Los datos no se sincronizan
1. Verifica que haya conexión a internet
2. Revisa que las rutas de sincronización estén accesibles
3. Comprueba que los métodos CSRF estén correctos

### El cache no funciona
1. Fuerza la recarga del service worker (Application > Service Workers > Unregister)
2. Limpia el cache del navegador
3. Recarga la página

## Seguridad

- Los datos offline se almacenan localmente en el navegador
- No se envían datos sensibles al servidor mientras esté offline
- Las credenciales de usuario se mantienen en sesión normal
- Los datos se encriptan automáticamente por el navegador

## Notas Importantes

- Las operaciones offline se guardan localmente y se sincronizan cuando hay conexión
- Si hay conflictos de datos (múltiples usuarios offline), prevalece la última escritura
- El sistema mantiene el control de turnos y usuarios como en el modo online
- Las validaciones de datos se realizan tanto offline como online

## Soporte

Para soporte o reporte de bugs, contacta al desarrollador o revisa la consola del navegador para errores específicos.