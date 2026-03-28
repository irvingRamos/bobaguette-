import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Heartbeat para mantener la sesión activa y evitar "Page Expired"
setInterval(() => {
    axios.get('/session-heartbeat').catch(error => {
        console.log('Session heartbeat failed:', error);
    });
}, 1000 * 60 * 30); // Cada 30 minutos
