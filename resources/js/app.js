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
