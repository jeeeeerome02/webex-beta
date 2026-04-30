import {createApp} from 'vue';

//Import components
import NavBar from './components/Navbar.vue';
import LoginButton from './components/LoginButton.vue';

//Import themes
import PrimeVue from 'primevue/config';
import 'primeicons/primeicons.css';
import Aura from '@primeuix/themes/aura';


const app = createApp({});

//For frameworks
app.use(PrimeVue, {
    theme: {
        preset: Aura,
    },
});

//Components
app.component('Navbar', NavBar);
app.component('LoginButton', LoginButton);

//Runtime
app.mount('#app');
