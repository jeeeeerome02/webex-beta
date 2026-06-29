<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Drawer from 'primevue/drawer';
import AppLogo from './AppLogo.vue';

const router = useRouter();
const route = useRoute();

const user = ref(null);
const mobileOpen = ref(false);
const isLoggingOut = ref(false);

const menu = [
    { label: 'Overview', icon: 'pi pi-th-large', to: '/dashboard' },
    { label: 'Classes', icon: 'pi pi-book', to: '/dashboard/classes' },
    { label: 'Chat', icon: 'pi pi-comments', to: '/dashboard/chat' },
    { label: 'Profile', icon: 'pi pi-user', to: '/dashboard/profile' },
];

const pageTitle = computed(() => menu.find((m) => m.to === route.path)?.label || 'Dashboard');
const initials = computed(() =>
    (user.value?.name || 'U')
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
);

const isActive = (to) => route.path === to;

const go = (to) => {
    mobileOpen.value = false;
    router.push(to);
};

const logout = async () => {
    isLoggingOut.value = true;
    try {
        await axios.post('/logout');
        window.dispatchEvent(new CustomEvent('auth:changed', { detail: null }));
        await router.push('/login');
    } finally {
        isLoggingOut.value = false;
    }
};

const loadUser = async () => {
    try {
        const { data } = await axios.get('/auth/user');
        user.value = data.user;
    } catch {
        user.value = null;
    }
};

onMounted(() => {
    loadUser();
    window.addEventListener('auth:changed', (e) => (user.value = e.detail || null));
});
onUnmounted(() => {
    window.removeEventListener('auth:changed', () => {});
});
</script>

<template>
    <div class="dash">
        <!-- Sidebar (desktop) -->
        <aside class="dash-sidebar">
            <div class="dash-brand">
                <AppLogo :size="34" :textSize="18" />
            </div>
            <nav class="dash-nav">
                <button
                    v-for="item in menu"
                    :key="item.to"
                    class="dash-nav-item"
                    :class="{ active: isActive(item.to) }"
                    @click="go(item.to)"
                >
                    <i :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                </button>
            </nav>
            <Button
                label="Logout"
                icon="pi pi-sign-out"
                class="dash-logout"
                severity="secondary"
                outlined
                :loading="isLoggingOut"
                @click="logout"
            />
        </aside>

        <!-- Mobile drawer -->
        <Drawer v-model:visible="mobileOpen" class="dash-drawer">
            <div class="dash-brand"><AppLogo :size="32" :textSize="17" /></div>
            <nav class="dash-nav">
                <button
                    v-for="item in menu"
                    :key="item.to"
                    class="dash-nav-item"
                    :class="{ active: isActive(item.to) }"
                    @click="go(item.to)"
                >
                    <i :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                </button>
            </nav>
        </Drawer>

        <!-- Main -->
        <div class="dash-main">
            <header class="dash-topbar">
                <div class="dash-topbar-left">
                    <Button
                        icon="pi pi-bars"
                        text
                        rounded
                        class="dash-burger"
                        aria-label="Open menu"
                        @click="mobileOpen = true"
                    />
                    <h1 class="dash-title">{{ pageTitle }}</h1>
                </div>
                <div class="dash-topbar-right">
                    <span class="dash-user">
                        <span class="dash-user-name">{{ user?.name || 'Guest' }}</span>
                        <Avatar :label="initials" shape="circle" />
                    </span>
                </div>
            </header>

            <main class="dash-content">
                <router-view />
            </main>
        </div>
    </div>
</template>

<style scoped>
.dash {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: var(--page-bg);
    color: var(--page-text);
}

/* Sidebar */
.dash-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.5rem 1rem;
    border-right: 1px solid var(--surface-border);
    background: var(--content-bg);
    position: sticky;
    top: 0;
    height: 100vh;
}

.dash-brand {
    padding: 0.5rem 0.75rem 1rem;
}

.dash-nav {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
}

.dash-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.9rem;
    border: none;
    border-radius: 10px;
    background: transparent;
    color: var(--muted-text);
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    text-align: left;
    transition: background 0.18s ease, color 0.18s ease;
}

.dash-nav-item i {
    font-size: 1.05rem;
}

.dash-nav-item:hover {
    background: var(--navbar-hover);
    color: var(--page-text);
}

.dash-nav-item.active {
    background: var(--button-primary-bg);
    color: var(--button-primary-text);
}

.dash-logout {
    width: 100%;
}

/* Main */
.dash-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.dash-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem clamp(1rem, 3vw, 2rem);
    border-bottom: 1px solid var(--surface-border);
    position: sticky;
    top: 0;
    background: color-mix(in srgb, var(--content-bg) 88%, transparent);
    backdrop-filter: blur(12px);
    z-index: 10;
}

.dash-topbar-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dash-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.01em;
}

.dash-burger {
    display: none;
}

.dash-user {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.dash-user-name {
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--muted-text);
}

.dash-content {
    padding: clamp(1.25rem, 3vw, 2rem);
    flex: 1;
}

.dash-drawer { display: none; }

@media (max-width: 900px) {
    .dash {
        grid-template-columns: 1fr;
    }
    .dash-sidebar {
        display: none;
    }
    .dash-burger {
        display: inline-flex;
    }
}
</style>
