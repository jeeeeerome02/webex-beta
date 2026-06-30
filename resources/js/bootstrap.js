import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Redirect to login on unauthenticated API responses (e.g. expired session, incognito).
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;
        const url = error.config?.url || '';
        const path = window.location.pathname;
        if (status === 401 && !url.includes('/auth/user') && !url.includes('/login') && path.startsWith('/dashboard')) {
            window.location.assign('/login');
        }
        return Promise.reject(error);
    }
);
