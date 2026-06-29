<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';

const router = useRouter();
const items = ref([]);
const loading = ref(true);

const load = async () => {
    try {
        const { data } = await axios.get('/api/notifications');
        items.value = data.items || [];
    } finally {
        loading.value = false;
    }
};

const open = (n) => {
    if (n.unread) { n.unread = false; axios.post('/api/notifications/read', { id: n.id }); window.dispatchEvent(new CustomEvent('notif:read')); }
    if (n.to) router.push(n.to);
};

const busy = ref({});
const accept = async (n) => {
    if (busy.value[n.id]) return;
    busy.value[n.id] = true;
    try {
        await axios.post(`/api/users/${n.user_id}/friend/accept`);
        items.value = items.value.filter((x) => x.id !== n.id);
        window.dispatchEvent(new CustomEvent('notif:refresh'));
    } finally { busy.value[n.id] = false; }
};
const decline = async (n) => {
    if (busy.value[n.id]) return;
    busy.value[n.id] = true;
    try {
        await axios.post(`/api/users/${n.user_id}/friend/decline`);
        items.value = items.value.filter((x) => x.id !== n.id);
        window.dispatchEvent(new CustomEvent('notif:refresh'));
    } finally { busy.value[n.id] = false; }
};

const clearAll = async () => {
    await axios.post('/api/notifications/clear');
    items.value = items.value.map((n) => ({ ...n, unread: false }));
    window.dispatchEvent(new CustomEvent('notif:refresh'));
};

onMounted(load);
</script>

<template>
    <div class="notifs">
        <div class="notif-head">
            <h3>Notifications</h3>
            <Button label="Clear all" icon="pi pi-check" text size="small" @click="clearAll" />
        </div>
        <p v-if="loading">Loading…</p>
        <template v-else>
            <button v-for="n in items" :key="n.id" class="notif" :class="[n.type, { unread: n.unread }]" @click="open(n)">
                <i :class="n.icon"></i>
                <div class="n-body">
                    <p>{{ n.text }}</p>
                    <span>{{ n.class ? n.class + ' · ' : '' }}{{ n.date }}</span>
                    <span v-if="n.type === 'request'" class="fr-actions" @click.stop>
                        <Button label="Accept" icon="pi pi-check" size="small" :loading="busy[n.id]" @click="accept(n)" />
                        <Button label="Decline" icon="pi pi-times" size="small" outlined :loading="busy[n.id]" @click="decline(n)" />
                    </span>
                </div>
                <span v-if="n.unread" class="dot"></span>
            </button>
            <p v-if="!items.length" class="empty">You're all caught up.</p>
        </template>
    </div>
</template>

<style scoped>
.notifs { width: 100%; display: flex; flex-direction: column; gap: 0.6rem; }
.notif-head { display: flex; justify-content: space-between; align-items: center; }
.notif { width: 100%; text-align: left; display: flex; gap: 0.75rem; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--surface-border); border-radius: 12px; background: var(--content-bg); cursor: pointer; transition: background 0.2s; }
.notif:hover { border-color: var(--accent, #4f46e5); }
.notif.unread { background: rgba(24, 119, 242, 0.08); }
.notif i { font-size: 1.25rem; width: 32px; text-align: center; }
.notif.award i { color: #f59e0b; }
.notif.friend i { color: #10b981; }
.notif.request i { color: #1877f2; }
.notif.group i { color: #4f46e5; }
.n-body { flex: 1; }
.n-body p { margin: 0; font-weight: 600; }
.n-body span { font-size: 0.75rem; color: var(--muted-text); }
.fr-actions { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
.dot { width: 10px; height: 10px; border-radius: 50%; background: #1877f2; }
.empty { color: var(--muted-text); }
</style>
