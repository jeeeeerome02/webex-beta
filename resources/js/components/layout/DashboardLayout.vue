<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Drawer from 'primevue/drawer';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import AppLogo from './AppLogo.vue';
import UserAvatar from '../common/UserAvatar.vue';

const router = useRouter();
const route = useRoute();
const toast = useToast();

const user = ref(null);
const mobileOpen = ref(false);
const isLoggingOut = ref(false);
let pollTimer = null;

const menu = [
    { label: 'Overview', icon: 'pi pi-th-large', to: '/dashboard' },
    { label: 'Classes', icon: 'pi pi-book', to: '/dashboard/classes', key: 'classes' },
    { label: 'Search', icon: 'pi pi-search', to: '/dashboard/search' },
    { label: 'Notifications', icon: 'pi pi-bell', to: '/dashboard/notifications', key: 'notifications' },
    { label: 'Chat', icon: 'pi pi-comments', to: '/dashboard/chat', key: 'chat' },
    { label: 'Profile', icon: 'pi pi-user', to: '/dashboard/profile' },
];

const notifications = ref({ classes: 0, notifications: 0, chat: 0 });
let knownFriends = null;

const loadNotifications = async () => {
    try {
        const { data } = await axios.get('/api/notifications');
        notifications.value = data;
        const accepted = (data.items || []).filter((n) => n.type === 'friend').map((n) => n.id);
        if (knownFriends !== null) {
            accepted.filter((id) => !knownFriends.includes(id)).forEach((id) => {
                const n = data.items.find((x) => x.id === id);
                toast.add({ severity: 'success', summary: 'Friend request accepted', detail: n.text, life: 4000 });
            });
        }
        knownFriends = accepted;
    } catch {
        notifications.value = { classes: 0, notifications: 0, chat: 0 };
    }
};

const pageTitle = computed(() => menu.find((m) => m.to === route.path)?.label || (route.name === 'DashboardClassDetail' ? 'Class' : 'Dashboard'));
const showBack = computed(() => route.name === 'DashboardClassDetail');
const decNotif = () => { if (notifications.value.notifications > 0) notifications.value.notifications -= 1; };
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
    loadNotifications();
    pollTimer = setInterval(loadNotifications, 20000);
    window.addEventListener('auth:changed', (e) => (user.value = e.detail || null));
    window.addEventListener('notif:read', decNotif);
    window.addEventListener('notif:refresh', loadNotifications);
    window.addEventListener('profile:updated', loadUser);
});
onUnmounted(() => {
    clearInterval(pollTimer);
    window.removeEventListener('auth:changed', () => {});
    window.removeEventListener('notif:read', decNotif);
    window.removeEventListener('notif:refresh', loadNotifications);
    window.removeEventListener('profile:updated', loadUser);
});
</script>

<template>
    <div class="dash">
        <Toast position="top-right" />
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
                    <span v-if="item.key && notifications[item.key]" class="dash-badge">{{ notifications[item.key] }}</span>
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
                    <span v-if="item.key && notifications[item.key]" class="dash-badge">{{ notifications[item.key] }}</span>
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
                    <Button
                        v-if="showBack"
                        icon="pi pi-arrow-left"
                        text
                        rounded
                        aria-label="Back to classes"
                        @click="router.push('/dashboard/classes')"
                    />
                    <h1 class="dash-title">{{ pageTitle }}</h1>
                </div>
                <div class="dash-topbar-right">
                    <span class="dash-user">
                        <span class="dash-user-name">{{ user?.name || 'Guest' }}</span>
                        <UserAvatar :src="user?.avatar_url" :name="user?.name" :size="38" />
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

.dash-badge {
    margin-left: auto;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    background: #ef4444;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    display: grid;
    place-items: center;
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
