import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
import Homepage from './pages/Homepage.vue';
import LoginPage from './pages/LoginPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import LeftAuthPanel from './components/layout/LeftAuthPanel.vue';
import DashboardLayout from './components/layout/DashboardLayout.vue';
import DashboardHome from './pages/dashboard/DashboardHome.vue';
import DashboardProfile from './pages/dashboard/DashboardProfile.vue';
import DashboardPlaceholder from './pages/dashboard/DashboardPlaceholder.vue';
import DashboardClasses from './pages/dashboard/DashboardClasses.vue';
import DashboardClassDetail from './pages/dashboard/DashboardClassDetail.vue';
import DashboardUserProfile from './pages/dashboard/DashboardUserProfile.vue';
import DashboardNotifications from './pages/dashboard/DashboardNotifications.vue';
import DashboardSearch from './pages/dashboard/DashboardSearch.vue';
import DashboardChat from './pages/dashboard/DashboardChat.vue';
import ClassInvite from './pages/ClassInvite.vue';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Homepage,
        meta: {
            title: null,
        },
    },
    {
        path: '/login',
        name: 'Login',
        components: {
            left: LeftAuthPanel,
            right: LoginPage
        },
        meta: {
            title: 'Login',
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
            title: 'Register',
            leftTitle: 'Join Our Community',
            leftDescription: 'Create your account and start exploring smarter examination tools. Let AI transform your assessment experience.'
        }
    },
    {
        path: '/dashboard',
        component: DashboardLayout,
        meta: { title: 'Dashboard', requiresAuth: true },
        children: [
            { path: '', name: 'Dashboard', component: DashboardHome },
            { path: 'classes', name: 'DashboardClasses', component: DashboardClasses },
            { path: 'classes/:id', name: 'DashboardClassDetail', component: DashboardClassDetail },
            { path: 'users/:id', name: 'DashboardUserProfile', component: DashboardUserProfile },
            { path: 'chat', name: 'DashboardChat', component: DashboardChat },
            { path: 'notifications', name: 'DashboardNotifications', component: DashboardNotifications },
            { path: 'search', name: 'DashboardSearch', component: DashboardSearch },
            { path: 'profile', name: 'DashboardProfile', component: DashboardProfile },
        ],
    },
    {
        path: '/class/:token',
        name: 'ClassInvite',
        component: ClassInvite,
        meta: { title: 'Class Invite' },
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    if (!to.meta.requiresAuth) {
        return true;
    }

    try {
        const { data } = await axios.get('/auth/user');
        if (data.user) {
            return true;
        }
    } catch {
        // fall through to redirect
    }

    return { name: 'Login' };
});

router.afterEach((to) => {
    const appName = import.meta.env.VITE_APP_NAME || 'Webex - Online Examination System';
    document.title = to.meta.title ? `${to.meta.title} - ${appName}` : appName;
});

export default router;
