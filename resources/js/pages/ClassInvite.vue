<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
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
const isMember = computed(() => membership.value === 'owner' || membership.value === 'approved');
const isPending = computed(() => membership.value === 'pending');

const load = async () => {
    try {
        const { data } = await axios.get(`/api/classrooms/invite/${token}`);
        cls.value = data.classroom;
        membership.value = data.membership;
        if (membership.value === 'owner' || membership.value === 'approved') {
            router.replace(`/dashboard/classes/${token}`);
            return;
        }
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
        if (data.status === 'approved' || data.status === 'owner') {
            setTimeout(() => router.push(`/dashboard/classes/${token}`), 600);
        }
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
        <header class="topbar">
            <AppLogo :size="32" :textSize="20" />
        </header>

        <p v-if="loading" class="center">Loading…</p>
        <p v-else-if="error" class="center err">{{ error }}</p>

        <div v-else class="cd">
            <header class="cd-hero">
                <span class="badge">{{ cls.type === 'public' ? 'Public class' : 'Private class' }}</span>
                <h1>{{ cls.name }}</h1>
                <p>by {{ cls.teacher }} · {{ cls.description || 'No description' }}</p>
            </header>

            <Tabs value="0">
                <TabList>
                    <Tab value="0"><i class="pi pi-clock"></i> Timeline</Tab>
                    <Tab value="1"><i class="pi pi-users"></i> Members</Tab>
                    <Tab value="2"><i class="pi pi-book"></i> Modules</Tab>
                    <Tab value="3"><i class="pi pi-check-square"></i> Tasks</Tab>
                    <Tab value="4"><i class="pi pi-comments"></i> Group Chat</Tab>
                </TabList>
            </Tabs>

            <div class="locked">
                <div class="blur">
                    <div class="skeleton card"></div>
                    <div class="skeleton card"></div>
                    <div class="skeleton card short"></div>
                </div>

                <div class="overlay">
                    <i class="pi pi-lock lock"></i>
                    <template v-if="isMember">
                        <p class="ok">Joined successfully.</p>
                        <Button label="Open class" icon="pi pi-arrow-right"
                            @click="router.push(`/dashboard/classes/${token}`)" />
                    </template>
                    <template v-else-if="isPending">
                        <h3>Request sent</h3>
                        <p class="wait">Please wait for the teacher to approve your request.</p>
                    </template>
                    <template v-else>
                        <h3>This content is locked</h3>
                        <p class="sub">{{ cls.type === 'public' ? 'Join to reveal the class content.' : 'Request to join. The teacher must approve you.' }}</p>
                        <Button :label="cls.type === 'public' ? 'Join class' : 'Request to join'"
                            icon="pi pi-user-plus" :loading="joining" @click="join" />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.invite { min-height: 100vh; background: var(--page-bg); padding: 1.5rem; }
.topbar { display: flex; justify-content: center; margin-bottom: 2rem; }
.center { text-align: center; margin-top: 4rem; }
.cd { max-width: 760px; margin: 0 auto; }
.cd-hero { border-left: 5px solid var(--accent); padding: 0.25rem 0 0.25rem 1rem; margin-bottom: 1.25rem; }
.cd-hero h1 { margin: 0.25rem 0; font-size: 1.6rem; }
.cd-hero p { margin: 0; color: var(--muted-text); }
.badge { font-size: 0.75rem; font-weight: 600; color: var(--accent); text-transform: uppercase; }
.locked { position: relative; margin-top: 1rem; min-height: 320px; }
.blur { filter: blur(6px); pointer-events: none; display: flex; flex-direction: column; gap: 1rem; padding-top: 1rem; }
.skeleton { background: var(--content-bg); border: 1px solid var(--surface-border); border-radius: 12px; height: 120px; }
.skeleton.short { height: 70px; }
.overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.75rem; text-align: center; background: color-mix(in srgb, var(--page-bg) 60%, transparent); border-radius: 12px; }
.lock { font-size: 2rem; color: var(--accent); }
.overlay h3 { margin: 0; }
.sub, .wait { color: var(--muted-text); margin: 0; max-width: 320px; }
.ok { color: #10b981; font-weight: 600; }
.err { color: #ef4444; }
</style>
