<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';
import AppLogo from '../components/layout/AppLogo.vue';

const route = useRoute();
const router = useRouter();

const token = route.params.token;
const loading = ref(true);
const joining = ref(false);
const cls = ref(null);
const membership = ref(null);
const message = ref('');
const error = ref('');

const accent = computed(() => cls.value?.theme_color || '#4f46e5');

const load = async () => {
    try {
        const { data } = await axios.get(`/api/classrooms/invite/${token}`);
        cls.value = data.classroom;
        membership.value = data.membership;
    } catch {
        error.value = 'This invite link is invalid or expired.';
    } finally {
        loading.value = false;
    }
};

const join = async () => {
    joining.value = true;
    error.value = '';
    try {
        const { data } = await axios.post(`/api/classrooms/invite/${token}/join`);
        membership.value = data.status;
        message.value = data.message;
    } catch (e) {
        if (e.response?.status === 401) {
            router.push('/login');
            return;
        }
        error.value = e.response?.data?.message || 'Could not join. Please sign in first.';
    } finally {
        joining.value = false;
    }
};

onMounted(load);
</script>

<template>
    <div class="invite" :style="{ '--accent': accent }">
        <div class="invite-card">
            <AppLogo :size="36" :textSize="20" />
            <p v-if="loading">Loading…</p>
            <p v-else-if="error" class="err">{{ error }}</p>
            <template v-else>
                <div class="bar"></div>
                <span class="badge">{{ cls.type === 'public' ? 'Public class' : 'Private class' }}</span>
                <h1>{{ cls.name }}</h1>
                <p class="teacher">by {{ cls.teacher }}</p>
                <p class="desc">{{ cls.description || 'No description' }}</p>

                <p v-if="message" class="ok">{{ message }}</p>

                <Button v-if="membership === 'owner'" label="Open class" icon="pi pi-arrow-right"
                    @click="router.push('/dashboard/classes')" />
                <Button v-else-if="membership === 'approved'" label="Go to dashboard" icon="pi pi-check"
                    @click="router.push('/dashboard/classes')" />
                <span v-else-if="membership === 'pending'" class="pending">Request pending approval</span>
                <Button v-else :label="cls.type === 'public' ? 'Join class' : 'Request to join'"
                    icon="pi pi-user-plus" :loading="joining" @click="join" />
            </template>
        </div>
    </div>
</template>

<style scoped>
.invite { min-height: 100vh; display: grid; place-items: center; background: var(--page-bg); padding: 1.5rem; }
.invite-card { width: 100%; max-width: 420px; background: var(--content-bg); border: 1px solid var(--surface-border);
    border-radius: 16px; padding: 2rem; display: flex; flex-direction: column; align-items: center; gap: 0.6rem; text-align: center; }
.bar { width: 64px; height: 6px; border-radius: 6px; background: var(--accent); margin: 0.5rem 0; }
.badge { font-size: 0.75rem; font-weight: 600; color: var(--accent); text-transform: uppercase; }
.invite-card h1 { margin: 0; font-size: 1.5rem; }
.teacher { margin: 0; color: var(--muted-text); }
.desc { color: var(--muted-text); margin: 0.5rem 0 1rem; }
.ok { color: #10b981; }
.err { color: #ef4444; }
.pending { color: #f59e0b; font-weight: 600; }
</style>
