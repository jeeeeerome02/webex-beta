<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import Menubar from 'primevue/menubar';
import Button from 'primevue/button';
import AppLogo from './AppLogo.vue';

const items = ref([
    { label: 'Home' },
    { label: 'Features' },
    {
        label: 'About',
        items: [
            {
                label: 'Platform Overview',
                icon: 'pi pi-angle-right',
                command: () => {},
                description: 'A technical overview of Webex as a platform for real-time communication and collaboration.'
            },
            {
                label: 'Developer',
                icon: 'pi pi-angle-right',
                command: () => {},
                description: 'Webex is developed by Cisco Systems, a leader in networking and enterprise collaboration.'
            },
            {
                label: 'How it works',
                icon: 'pi pi-angle-right',
                command: () => {},
                description: 'Webex uses cloud-based infrastructure, secure protocols, and real-time media streaming for meetings and messaging.'
            }
        ]
    },
    { label: 'Contact' },
]);

const currentTheme = ref('light');
const isScrolled = ref(false);

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

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    const preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

    applyTheme(savedTheme || preferredTheme);
    updateNavbarState();
    window.addEventListener('scroll', updateNavbarState, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', updateNavbarState);
});
</script>

<template>
    <header class="navbar" :class="{ 'navbar-scrolled': isScrolled }">
        <Menubar :model="items">
            <template #start>
                <AppLogo />
            </template>
            <template #end>
                <div class="navbar-actions">

                    <Button label="Login" icon="pi pi-user" size="small" class="navbar-login" as="a" href="/login" />
                    <Button label="Register" icon="pi pi-user-plus" size="small" class="navbar-register" as="a" href="/register" />
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

.navbar :deep(.p-menubar) {
    width: 100%;
    height: 72px;
    padding: 0 150px;
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
    margin-right: 20px;
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
        padding: 0 20px;
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
/* Force PrimeVue Menubar submenu to show on hover */
.navbar :deep(.p-menubar-root-list > .p-menuitem:hover > .p-submenu-list) {
    display: block !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
</style>
