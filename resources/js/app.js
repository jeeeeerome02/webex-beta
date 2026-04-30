import { createApp } from 'vue';

// PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

// Root component
import App from './components/App.vue';

const app = createApp(App);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
    },
});

app.mount('#app');
