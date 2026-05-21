<script setup>
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Menubar from 'primevue/menubar';
import Button from 'primevue/button';
import AppLogo from './AppLogo.vue';

const router = useRouter();

const scrollToSection = async (sectionId) => {
    if (router.currentRoute.value.name !== 'Home') {
        await router.push('/');
        await nextTick();
    }

    window.requestAnimationFrame(() => {
        document.getElementById(sectionId)?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });
    });
};

const items = ref([
    { label: 'Home', command: () => scrollToSection('home') },
    { label: 'Features', command: () => scrollToSection('features') },
    {
        label: 'About',
        items: [
            {
                label: 'Platform Overview',
                icon: 'pi pi-angle-right',
                command: () => scrollToSection('about'),
                description: 'An AI-powered exam platform for creating, delivering, and grading assessments automatically.'
            },
            {
                label: 'How it works',
                icon: 'pi pi-angle-right',
                command: () => scrollToSection('features'),
                description: 'Create questions with AI, deliver exams to students, and review results in one seamless workflow.'
            },
            {
                label: 'Security',
                icon: 'pi pi-angle-right',
                command: () => scrollToSection('features'),
                description: 'End-to-end encryption and role-based access keep your exam data safe and compliant.'
            }
        ]
    },
    { label: 'Contact', command: () => scrollToSection('contact') },
]);

const currentTheme = ref('light');
const isScrolled = ref(false);
const currentUser = ref(null);
const isLoggingOut = ref(false);

const updateNavbarState = () => {
    isScrolled.value = window.scrollY > 12;
};

const applyTheme = (theme) => {
    currentTheme.value = theme;
    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle('app-dark', theme === 'dark');
    localStorage.setItem('theme', theme);
};

const toggleTheme = () => {
    applyTheme(currentTheme.value === 'dark' ? 'light' : 'dark');
};

const loadCurrentUser = async () => {
    try {
        const { data } = await axios.get('/auth/user');
        currentUser.value = data.user;
    } catch {
        currentUser.value = null;
    }
};

const handleAuthChange = (event) => {
    currentUser.value = event.detail || null;
};

const logout = async () => {
    isLoggingOut.value = true;

    try {
        await axios.post('/logout');
        currentUser.value = null;
        window.dispatchEvent(new CustomEvent('auth:changed', { detail: null }));

        if (['Login', 'Register'].includes(router.currentRoute.value.name)) {
            await router.push('/');
        }
    } finally {
        isLoggingOut.value = false;
    }
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    const preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

    applyTheme(savedTheme || preferredTheme);
    loadCurrentUser();
    updateNavbarState();
    window.addEventListener('scroll', updateNavbarState, { passive: true });
    window.addEventListener('auth:changed', handleAuthChange);
});

onUnmounted(() => {
    window.removeEventListener('scroll', updateNavbarState);
    window.removeEventListener('auth:changed', handleAuthChange);
});
</script>

<template>
    <header class="navbar" :class="{ 'navbar-scrolled': isScrolled }">
        <Menubar :model="items">
            <template #start>
                <AppLogo :size="40"/>
            </template>
            <template #end>
                <div class="navbar-end">
                    <div v-if="currentUser" class="navbar-user-actions">
                        <span class="navbar-user" :title="currentUser.email">
                            <i class="pi pi-user"></i>
                            <span>{{ currentUser.name }}</span>
                        </span>
                        <Button
                            label="Logout"
                            icon="pi pi-sign-out"
                            size="small"
                            class="navbar-login"
                            :loading="isLoggingOut"
                            @click="logout"
                        />
                    </div>
                    <div v-else class="navbar-actions">
                        <Button label="Login" icon="pi pi-user" size="small" class="navbar-login" @click="router.push('/login')" />
                        <Button label="Register" icon="pi pi-user-plus" size="small" class="navbar-register" @click="router.push('/register')" />
                    </div>
                    <Button
                            :icon="currentTheme === 'dark' ? 'pi pi-sun' : 'pi pi-moon'"
                            :aria-label="currentTheme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'"
                            text
                            rounded
                            size="small"
                            class="theme-toggle"
                            @click="toggleTheme"
                        />
                </div>
            </template>
        </Menubar>
    </header>
</template>

<style scoped>
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1000;
    background: transparent;
    border-bottom: none;
    backdrop-filter: blur(26px) saturate(185%) contrast(108%);
    -webkit-backdrop-filter: blur(26px) saturate(185%) contrast(108%);
    transition: background-color 0.2s ease, backdrop-filter 0.2s ease;
}

.navbar-scrolled {
    background: var(--navbar-bg);
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}

.navbar-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    flex: 0 0 auto;
}

.navbar-user-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
}

.navbar-user {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 220px;
    min-height: 2.25rem;
    padding: 0.45rem 0.75rem;
    border: 1px solid var(--surface-border);
    border-radius: 999px;
    color: var(--navbar-text);
    background: color-mix(in srgb, var(--navbar-bg) 82%, transparent);
    font-size: 0.875rem;
    font-weight: 600;
}

.navbar-user span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.navbar-end {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    flex: 0 0 auto;
}

.navbar :deep(.p-menubar) {
    width: 100%;
    max-width: 1280px;
    height: 72px;
    margin: 0 auto;
    padding: 0 clamp(1rem, 4vw, 2.5rem);
    border: none;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}

.navbar :deep(.p-menubar-start) {
    flex: 0 0 auto;
    margin-right: 1.75rem;
}

.navbar :deep(.p-menubar-root-list) {
    gap: 0.5rem;
    min-width: 0;
}

.navbar :deep(.p-menubar-item-link) {
    padding: 0.75rem 1rem;
    border-radius: 8px;
}

.navbar :deep(.p-menubar .p-menuitem-link),
.navbar :deep(.p-menubar .p-menuitem-text),
.navbar :deep(.p-menubar .p-menubar-item-link),
.navbar :deep(.p-menubar .p-menubar-item-label),
.navbar :deep(.p-menubar .pi),
.navbar :deep(.p-menubar-button) {
    color: var(--navbar-text);
}

.navbar :deep(.p-menubar-item-link:hover),
.navbar :deep(.p-menubar-item-link:focus) {
    background: var(--navbar-hover);
}

.navbar :deep(.p-menubar-item-content:hover),
.navbar :deep(.p-menubar-item.p-focus > .p-menubar-item-content),
.navbar :deep(.p-menubar-item-active > .p-menubar-item-content) {
    background: var(--navbar-hover);
    color: var(--navbar-text);
}

.navbar :deep(.p-menubar-item-content:hover .p-menuitem-text),
.navbar :deep(.p-menubar-item-content:hover .p-menubar-item-label),
.navbar :deep(.p-menubar-item.p-focus .p-menuitem-text),
.navbar :deep(.p-menubar-item.p-focus .p-menubar-item-label),
.navbar :deep(.p-menubar-item-active .p-menuitem-text) {
    color: var(--navbar-text);
}

.navbar:not(.navbar-scrolled) :deep(.p-menubar .p-menuitem-text),
.navbar:not(.navbar-scrolled) :deep(.p-menubar .p-menubar-item-link),
.navbar:not(.navbar-scrolled) :deep(.p-menubar .p-menubar-item-label),
.navbar:not(.navbar-scrolled) :deep(.theme-toggle .pi),
.navbar:not(.navbar-scrolled) :deep(.logo-text) {
    color: var(--navbar-text);
    opacity: 1;
}

.navbar :deep(.theme-toggle) {
    color: var(--navbar-text);
}

.navbar :deep(.theme-toggle:hover) {
    background: var(--navbar-hover);
}

.navbar :deep(.navbar-login) {
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    border-color: var(--button-primary-bg);
}

.navbar :deep(.navbar-login .pi) {
    color: var(--button-primary-text);
}

.navbar :deep(.navbar-login:hover) {
    color: var(--button-secondary-text);
    background: var(--button-secondary-bg);
    border-color: var(--button-border);
}

.navbar :deep(.navbar-login:hover .pi) {
    color: var(--button-secondary-text);
}

.navbar :deep(.navbar-register) {
    color: var(--button-secondary-text);
    background: var(--button-secondary-bg);
    border-color: var(--button-border);
}

.navbar :deep(.navbar-register .pi) {
    color: var(--button-secondary-text);
}

.navbar :deep(.navbar-register:hover) {
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    border-color: var(--button-primary-bg);
}

.navbar :deep(.navbar-register:hover .pi) {
    color: var(--button-primary-text);
}

.navbar:not(.navbar-scrolled) :deep(.navbar-register) {
    color: #111;
    background: #fff;
    border-color: #fff;
}

.navbar:not(.navbar-scrolled) :deep(.navbar-register .pi) {
    color: #111;
}

.navbar:not(.navbar-scrolled) :deep(.navbar-register:hover) {
    color: #fff;
    background: #111;
    border-color: #111;
}

.navbar:not(.navbar-scrolled) :deep(.navbar-register:hover .pi) {
    color: #fff;
}

/* Mobile: put hamburger button first, then logo */
@media (max-width: 960px) {
    .navbar {
        top: 0;
        left: 0;
        right: 0;
    }

    .navbar :deep(.p-menubar) {
        display: flex;
        align-items: center;
        min-height: 64px;
        height: auto;
        padding: 0 1rem;
    }

    .navbar :deep(.p-menubar-item-link) {
        padding: 0.75rem;
    }

    .navbar :deep(.p-menubar-button) {
        order: 1;
        margin-right: 0.5rem;
    }

    .navbar :deep(.p-menubar-start) {
        order: 2;
        margin-right: 0;
    }

    .navbar :deep(.p-menubar-end) {
        order: 3;
        margin-left: auto;
    }

}

@media (max-width: 640px) {
    .navbar-actions {
        gap: 0.375rem;
    }

    .navbar :deep(.navbar-login .p-button-label) {
        display: none;
    }

    .navbar :deep(.navbar-login),
    .navbar :deep(.navbar-register),
    .navbar :deep(.theme-toggle) {
        min-width: 2.5rem;
    }

    .navbar-user {
        max-width: 130px;
    }
}

@media (max-width: 460px) {
    .navbar-user span {
        display: none;
    }

    .navbar-user {
        padding-inline: 0.7rem;
    }

    .navbar :deep(.navbar-register .p-button-label) {
        display: none;
    }
}
/* Force PrimeVue Menubar submenu to show on hover */
.navbar :deep(.p-menubar-root-list > .p-menuitem:hover > .p-submenu-list) {
    display: block !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
</style>
