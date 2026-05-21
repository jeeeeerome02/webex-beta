import { createApp } from 'vue';
import './bootstrap';

// PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

// Root component
import App from './components/App.vue';

// Router
import router from './router.js';

const app = createApp(App);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.app-dark',
        },
    },
});

app.use(router);

app.mount('#app');
