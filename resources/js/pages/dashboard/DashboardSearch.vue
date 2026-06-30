<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import UserAvatar from '../../components/common/UserAvatar.vue';

const router = useRouter();
const q = ref('');
const results = ref([]);
const searching = ref(false);
let timer = null;

watch(q, (val) => {
    clearTimeout(timer);
    if (!val.trim()) { results.value = []; return; }
    timer = setTimeout(async () => {
        searching.value = true;
        try {
            const { data } = await axios.get('/api/users', { params: { q: val } });
            results.value = data.users;
        } finally { searching.value = false; }
    }, 300);
});

const openProfile = (u) => router.push(`/dashboard/users/${u.profile_token || u.id}`);
const busy = ref({});
const run = async (u, path, status) => {
    if (busy.value[u.id]) return;
    busy.value[u.id] = true;
    try {
        const { data } = await axios.post(`/api/users/${u.id}/friend${path}`);
        u.friend_status = status || data.status;
    } finally { busy.value[u.id] = false; }
};
const addFriend = (u) => run(u, '', null);
const cancelFriend = (u) => run(u, '/cancel', 'none');
const acceptFriend = (u) => run(u, '/accept', null);
const declineFriend = (u) => run(u, '/decline', 'none');
</script>

<template>
    <div class="search">
        <span class="search-box">
            <i class="pi pi-search"></i>
            <InputText v-model="q" placeholder="Search users by name or email…" autofocus />
        </span>
        <p v-if="searching" class="muted">Searching…</p>
        <div v-for="u in results" :key="u.id" class="row">
            <UserAvatar :src="u.avatar_url" :name="u.name" :size="40" style="cursor:pointer" @click="openProfile(u)" />
            <div class="info" @click="openProfile(u)"><strong>{{ u.name }}</strong><span>{{ u.role }} · {{ u.email }}</span></div>
            <Tag v-if="u.friend_status === 'friends'" value="Friends" icon="pi pi-check" severity="success" />
            <Button v-else-if="u.friend_status === 'requested'" label="Cancel" icon="pi pi-times" size="small" outlined :loading="busy[u.id]" @click="cancelFriend(u)" />
            <span v-else-if="u.friend_status === 'incoming'" class="fr-actions">
                <Button label="Accept" icon="pi pi-check" size="small" :loading="busy[u.id]" @click="acceptFriend(u)" />
                <Button label="Decline" icon="pi pi-times" size="small" outlined :loading="busy[u.id]" @click="declineFriend(u)" />
            </span>
            <Button v-else label="Add friend" icon="pi pi-user-plus" size="small" outlined :loading="busy[u.id]" @click="addFriend(u)" />
        </div>
        <p v-if="q && !results.length && !searching" class="muted">No users found.</p>
    </div>
</template>

<style scoped>
.search { width: 100%; display: flex; flex-direction: column; gap: 0.75rem; }
.search-box { display: flex; align-items: center; gap: 0.5rem; border: 1px solid var(--surface-border); border-radius: 10px; padding: 0 0.75rem; }
.search-box i { color: var(--muted-text); }
.search-box :deep(.p-inputtext) { border: none; box-shadow: none; flex: 1; background: transparent; }
.row { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border: 1px solid var(--surface-border); border-radius: 10px; }
.info { display: flex; flex-direction: column; flex: 1; cursor: pointer; }
.info span { font-size: 0.8rem; color: var(--muted-text); }
.muted { color: var(--muted-text); }
</style>
