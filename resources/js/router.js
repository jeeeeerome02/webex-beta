import { createRouter, createWebHistory } from 'vue-router';
import Homepage from './pages/Homepage.vue';
import LoginPage from './pages/LoginPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import LeftAuthPanel from './components/layout/LeftAuthPanel.vue';

const routes = [
    { 
        path: '/', 
        name: 'Home', 
        component: Homepage 
    },
    { 
        path: '/login', 
        name: 'Login',
        components: {
            left: LeftAuthPanel,
            right: LoginPage
        },
        meta: { 
            leftTitle: 'Smarter Online<br>Examination Starts Here',
            leftDescription: 'Let AI help you create questions and review results automatically. Run exams smoothly without the usual hassle.'
        }
    },
    { 
        path: '/register', 
        name: 'Register',
        components: {
            left: LeftAuthPanel,
            right: RegisterPage
        },
        meta: { 
            leftTitle: 'Join Our Community',
            leftDescription: 'Create your account and start exploring smarter examination tools. Let AI transform your assessment experience.'
        }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
