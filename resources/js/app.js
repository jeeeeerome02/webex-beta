import {createApp} from 'vue';

//Import components
import NavBar from './components/Navbar.vue';
import LoginButton from './components/LoginButton.vue';
import AppLogo from './components/AppLogo.vue';
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
app.component('AppLogo', AppLogo);
//Runtime
app.mount('#app');
