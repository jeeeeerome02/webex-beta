import { createApp } from 'vue';
import './bootstrap';

// PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import { definePreset } from '@primeuix/themes';
import 'primeicons/primeicons.css';
import 'quill/dist/quill.snow.css';

// Root component
import App from './components/App.vue';

// Router
import router from './router.js';

const Noir = definePreset(Aura, {
    semantic: {
        primary: {
            50: '#f5f5f5', 100: '#e9e9e9', 200: '#d4d4d4', 300: '#a3a3a3',
            400: '#525252', 500: '#111111', 600: '#0d0d0d', 700: '#0a0a0a',
            800: '#070707', 900: '#030303', 950: '#000000',
        },
        colorScheme: {
            light: {
                primary: {
                    color: '#111111', contrastColor: '#ffffff',
                    hoverColor: '#000000', activeColor: '#000000',
                },
            },
            dark: {
                primary: {
                    color: '#ffffff', contrastColor: '#000000',
                    hoverColor: '#e9e9e9', activeColor: '#d4d4d4',
                },
            },
        },
    },
});

const app = createApp(App);

app.use(PrimeVue, {
    theme: {
        preset: Noir,
        options: {
            darkModeSelector: '.app-dark',
        },
    },
});

app.use(router);

// Scroll-reveal directive: animates elements into view as they enter the viewport.
const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const revealObserver =
    typeof IntersectionObserver !== 'undefined'
        ? new IntersectionObserver(
              (entries, observer) => {
                  entries.forEach((entry) => {
                      if (entry.isIntersecting) {
                          entry.target.classList.add('is-revealed');
                          observer.unobserve(entry.target);
                      }
                  });
              },
              { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
          )
        : null;

app.directive('reveal', {
    mounted(el, binding) {
        el.classList.add('reveal');

        if (binding.value?.delay) {
            el.style.setProperty('--reveal-delay', `${binding.value.delay}ms`);
        }

        if (prefersReducedMotion || !revealObserver) {
            el.classList.add('is-revealed');
            return;
        }

        revealObserver.observe(el);
    },
    unmounted(el) {
        revealObserver?.unobserve(el);
    },
});

app.mount('#app');
