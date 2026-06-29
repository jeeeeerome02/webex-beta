<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const route = useRoute();
const router = useRouter();
const user = ref(null);
const awards = ref([]);
const teaching = ref([]);
const loading = ref(true);
const busy = ref(false);

const cover = computed(() => user.value?.cover_url || '');
const avatar = computed(() => user.value?.avatar_url || `https://api.dicebear.com/9.x/adventurer/svg?seed=${user.value?.name || 'User'}`);
const isTeacher = computed(() => user.value?.role === 'teacher');

const run = async (path, status) => {
    if (busy.value) return;
    busy.value = true;
    try {
        const { data } = await axios.post(`/api/users/${user.value.id}/friend${path}`);
        user.value.friend_status = status || data.status;
    } finally { busy.value = false; }
};
const addFriend = () => run('', null);
const cancelFriend = () => run('/cancel', 'none');
const acceptFriend = () => run('/accept', null);
const declineFriend = () => run('/decline', 'none');
const unfriend = () => { if (confirm(`Unfriend ${user.value.name}?`)) run('/unfriend', 'none'); };

const message = async () => {
    const { data } = await axios.get(`/api/conversations/with/${user.value.id}`);
    router.push({ path: '/dashboard/chat', query: { c: data.conversation.id } });
};

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/users/${route.params.id}`);
        user.value = data.user;
        awards.value = data.awards;
        teaching.value = data.teaching || [];
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div v-if="user" class="profile">
        <div class="cover" :style="cover ? { backgroundImage: `url(${cover})` } : {}">
            <div class="avatar-wrap">
                <img :src="avatar" class="avatar" alt="avatar" />
            </div>
        </div>

        <div class="head">
            <h2>{{ user.name }}</h2>
            <p class="role">{{ user.role }} · {{ user.course || 'No course' }}</p>
            <div class="stats">
                <span><strong>{{ user.friends_count }}</strong> friends</span>
                <span v-if="!isTeacher"><strong>{{ awards.length }}</strong> awards</span>
            </div>
            <div v-if="!user.is_self" class="friend-bar">
                <template v-if="user.friend_status === 'friends'">
                    <Tag value="Friends" icon="pi pi-check" severity="success" />
                    <Button label="Unfriend" icon="pi pi-user-minus" size="small" severity="danger" outlined :loading="busy" @click="unfriend" />
                </template>
                <Button v-else-if="user.friend_status === 'requested'" label="Cancel request" icon="pi pi-times" size="small" outlined :loading="busy" @click="cancelFriend" />
                <template v-else-if="user.friend_status === 'incoming'">
                    <Button label="Accept" icon="pi pi-check" size="small" :loading="busy" @click="acceptFriend" />
                    <Button label="Decline" icon="pi pi-times" size="small" outlined :loading="busy" @click="declineFriend" />
                </template>
                <Button v-else label="Add friend" icon="pi pi-user-plus" size="small" outlined :loading="busy" @click="addFriend" />
                <Button label="Message" icon="pi pi-comments" size="small" @click="message" />
            </div>
        </div>

        <template v-if="isTeacher">
            <h3 class="sec">Teaching</h3>
            <div class="awards">
                <div v-for="c in teaching" :key="c.id" class="award">
                    <i class="pi pi-book award-icon"></i>
                    <strong>{{ c.name }}</strong>
                    <span>{{ c.course_type || 'Course' }}</span>
                </div>
                <p v-if="!teaching.length" class="empty">No active classes.</p>
            </div>
        </template>
        <template v-else>
            <h3 class="sec">Awards</h3>
            <div class="awards">
                <div v-for="a in awards" :key="a.id" class="award">
                    <i :class="a.icon" class="award-icon"></i>
                    <strong>{{ a.label }}</strong>
                    <span>{{ a.class }} · {{ a.date }}</span>
                </div>
                <p v-if="!awards.length" class="empty">No awards yet.</p>
            </div>
        </template>
    </div>
    <p v-else-if="loading">Loading…</p>
</template>

<style scoped>
.profile { max-width: 100%; }
.cover { position: relative; height: 200px; background: linear-gradient(135deg, #4f46e5, #0ea5e9); background-size: cover; background-position: center; border-radius: 16px; }
.avatar-wrap { position: absolute; bottom: -45px; left: 1.5rem; }
.avatar { width: 110px; height: 110px; border-radius: 50%; border: 4px solid var(--content-bg); background: var(--content-bg); object-fit: cover; }
.head { margin: 3.5rem 0 0 1.5rem; }
.head h2 { margin: 0; }
.role { margin: 0.25rem 0; color: var(--muted-text); text-transform: capitalize; }
.stats { display: flex; gap: 1.5rem; margin-top: 0.5rem; }
.stats strong { color: var(--page-text); }
.friend-bar { display: flex; gap: 0.5rem; align-items: center; margin-top: 0.75rem; flex-wrap: wrap; }
.sec { margin: 1.5rem 0 0.75rem; }
.awards { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
.award { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; text-align: center; background: var(--content-bg); border: 1px solid var(--surface-border); border-radius: 12px; padding: 1rem; }
.award-icon { font-size: 1.75rem; color: #f59e0b; animation: shine 2s ease-in-out infinite; filter: drop-shadow(0 0 6px rgba(245,158,11,0.7)); }
@keyframes shine { 0%,100% { transform: scale(1); } 50% { transform: scale(1.2); } }
.award span { font-size: 0.75rem; color: var(--muted-text); }
.empty { color: var(--muted-text); }
</style>
