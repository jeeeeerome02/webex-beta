<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';

const route = useRoute();
const router = useRouter();
const user = ref(null);
const awards = ref([]);
const teaching = ref([]);
const loading = ref(true);
const busy = ref(false);
const showUnfriend = ref(false);

const cover = computed(() => user.value?.cover_url || '');
const avatar = computed(() => user.value?.avatar_url || `https://api.dicebear.com/9.x/adventurer/svg?seed=${user.value?.name || 'User'}`);
const isTeacher = computed(() => user.value?.role === 'teacher');
const locationText = computed(() => {
    const parts = [user.value?.city_municipality, user.value?.province, user.value?.country].filter(Boolean);
    return parts.join(', ');
});

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
const unfriend = () => { showUnfriend.value = false; if (confirm(`Unfriend ${user.value.name}?`)) run('/unfriend', 'none'); };

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
            <p class="role">{{ user.role }}</p>
            <p v-if="isTeacher && user.subject" class="detail"><i class="pi pi-bookmark"></i> Teaching {{ user.subject }}</p>
            <p v-else-if="!isTeacher && user.course" class="detail"><i class="pi pi-graduation-cap"></i> Your course is {{ user.course }}</p>
            <p v-if="locationText" class="detail"><i class="pi pi-map-marker"></i> {{ locationText }}</p>
            <div class="stats">
                <span><strong>{{ user.friends_count }}</strong> friends</span>
                <span v-if="!isTeacher"><strong>{{ awards.length }}</strong> awards</span>
                <span v-else><strong>{{ teaching.length }}</strong> classes</span>
            </div>
            <div v-if="!user.is_self" class="friend-bar">
                <template v-if="user.friend_status === 'friends'">
                    <div class="friend-dd">
                        <button class="friend-badge" @click="showUnfriend = !showUnfriend">
                            <i class="pi pi-check"></i> Friends <i class="pi pi-angle-down"></i>
                        </button>
                        <div v-if="showUnfriend" class="friend-menu">
                            <button class="friend-menu-item" @click="unfriend">
                                <i class="pi pi-user-minus"></i> Unfriend
                            </button>
                        </div>
                    </div>
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
                    <span>{{ c.position }}{{ c.course_type ? ' · ' + c.course_type : '' }}</span>
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
.detail { margin: 0.2rem 0; color: var(--muted-text); font-size: 0.9rem; display: flex; align-items: center; gap: 0.45rem; }
.detail i { color: var(--accent, #4f46e5); }
.stats { display: flex; gap: 1.5rem; margin-top: 0.5rem; }
.stats strong { color: var(--page-text); }
.friend-bar { display: flex; gap: 0.5rem; align-items: center; margin-top: 0.75rem; flex-wrap: wrap; }
.friend-dd { position: relative; }
.friend-badge { display: inline-flex; align-items: center; gap: 0.35rem; background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.4rem 0.7rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
.friend-menu { position: absolute; top: calc(100% + 4px); left: 0; background: var(--content-bg); border: 1px solid var(--surface-border); border-radius: 8px; box-shadow: 0 6px 20px rgba(0,0,0,0.15); z-index: 20; overflow: hidden; }
.friend-menu-item { display: flex; align-items: center; gap: 0.5rem; width: 100%; padding: 0.6rem 1rem; background: transparent; border: none; cursor: pointer; color: #ef4444; white-space: nowrap; font-size: 0.85rem; }
.friend-menu-item:hover { background: var(--surface-border); }
.sec { margin: 1.5rem 0 0.75rem; }
.awards { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
.award { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; text-align: center; background: var(--content-bg); border: 1px solid var(--surface-border); border-radius: 12px; padding: 1rem; }
.award-icon { font-size: 1.75rem; color: #f59e0b; animation: shine 2s ease-in-out infinite; filter: drop-shadow(0 0 6px rgba(245,158,11,0.7)); }
@keyframes shine { 0%,100% { transform: scale(1); } 50% { transform: scale(1.2); } }
.award span { font-size: 0.75rem; color: var(--muted-text); }
.empty { color: var(--muted-text); }
</style>
