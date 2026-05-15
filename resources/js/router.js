import { createRouter, createWebHistory } from 'vue-router';
import Homepage from './pages/Homepage.vue';
import LoginPage from './pages/LoginPage.vue';

const routes = [
  { path: '/', name: 'Home', component: Homepage },
  { path: '/login', name: 'Login', component: LoginPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
