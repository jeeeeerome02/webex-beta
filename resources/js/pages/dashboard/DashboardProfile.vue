<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const user = ref(null);
const awards = ref([]);
const showPicker = ref(false);
const target = ref('avatar_url');
const saving = ref(false);
const fileInput = ref(null);

const avatarSeeds = ['Felix', 'Aneka', 'Milo', 'Zoe', 'Leo', 'Mia', 'Kai', 'Nova', 'Theo', 'Luna'];
const defaultAvatars = avatarSeeds.map((s) => `https://api.dicebear.com/9.x/adventurer/svg?seed=${s}`);

const cover = computed(() => user.value?.cover_url || '');
const avatar = computed(() => user.value?.avatar_url || `https://api.dicebear.com/9.x/adventurer/svg?seed=${user.value?.name || 'User'}`);

const load = async () => {
    const { data: me } = await axios.get('/auth/user');
    const { data } = await axios.get(`/api/users/${me.user.id}`);
    user.value = data.user;
    awards.value = data.awards;
};

const openPicker = (which) => { target.value = which; showPicker.value = true; };

const chooseDefault = async (url) => {
    user.value[target.value] = url;
    showPicker.value = false;
    await save();
};

const onFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 25 * 1024 * 1024) { alert('Max 25MB.'); return; }
    const reader = new FileReader();
    reader.onload = async () => { user.value[target.value] = reader.result; showPicker.value = false; await save(); };
    reader.readAsDataURL(file);
};

const save = async () => {
    saving.value = true;
    try {
        await axios.put('/api/profile', { avatar_url: user.value.avatar_url, cover_url: user.value.cover_url });
        window.dispatchEvent(new CustomEvent('profile:updated'));
    } finally { saving.value = false; }
};

onMounted(load);
</script>

<template>
    <div v-if="user" class="profile">
        <div class="cover" :style="cover ? { backgroundImage: `url(${cover})` } : {}">
            <Button icon="pi pi-camera" rounded class="cover-edit" @click="openPicker('cover_url')" />
            <div class="avatar-wrap">
                <img :src="avatar" class="avatar" alt="avatar" />
                <Button icon="pi pi-camera" rounded size="small" class="avatar-edit" @click="openPicker('avatar_url')" />
            </div>
        </div>

        <div class="head">
            <h2>{{ user.name }}</h2>
            <p class="role">{{ user.role }} · {{ user.course || 'No course' }}</p>
            <div class="stats">
                <span><strong>{{ user.friends_count }}</strong> friends</span>
                <span><strong>{{ awards.length }}</strong> awards</span>
            </div>
        </div>

        <h3 class="sec">Awards</h3>
        <div class="awards">
            <div v-for="a in awards" :key="a.id" class="award">
                <i :class="a.icon" class="award-icon"></i>
                <strong>{{ a.label }}</strong>
                <span>{{ a.class }} · {{ a.date }}</span>
            </div>
            <p v-if="!awards.length" class="empty">No awards yet.</p>
        </div>

        <Dialog v-model:visible="showPicker" modal header="Choose a photo" :style="{ width: '440px' }">
            <p class="pick-label">Pick a default avatar</p>
            <div class="grid">
                <button v-for="u in defaultAvatars" :key="u" class="pick" @click="chooseDefault(u)">
                    <img :src="u" alt="avatar" />
                </button>
            </div>
            <p class="pick-label">Or upload your own</p>
            <button class="upload-btn" @click="fileInput.click()">
                <i class="pi pi-image"></i>
                <span>Choose an image</span>
                <small>JPG, PNG · max 25MB</small>
            </button>
            <input ref="fileInput" type="file" accept="image/*" class="hidden-input" @change="onFile" />
        </Dialog>
    </div>
</template>

<style scoped>
.profile { max-width: 100%; }
.cover { position: relative; height: 200px; background: linear-gradient(135deg, #4f46e5, #0ea5e9); background-size: cover; background-position: center; border-radius: 16px; }
.cover-edit { position: absolute; top: 0.75rem; right: 0.75rem; }
.avatar-wrap { position: absolute; bottom: -45px; left: 1.5rem; }
.avatar { width: 110px; height: 110px; border-radius: 50%; border: 4px solid var(--content-bg); background: var(--content-bg); object-fit: cover; }
.avatar-edit { position: absolute; bottom: 0; right: 0; }
.head { margin: 3.5rem 0 0 1.5rem; }
.head h2 { margin: 0; }
.role { margin: 0.25rem 0; color: var(--muted-text); text-transform: capitalize; }
.stats { display: flex; gap: 1.5rem; margin-top: 0.5rem; }
.stats strong { color: var(--page-text); }
.sec { margin: 1.5rem 0 0.75rem; }
.awards { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
.award { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; text-align: center; background: var(--content-bg); border: 1px solid var(--surface-border); border-radius: 12px; padding: 1rem; }
.award-icon { font-size: 1.75rem; color: #f59e0b; animation: shine 2s ease-in-out infinite; filter: drop-shadow(0 0 6px rgba(245,158,11,0.7)); }
@keyframes shine { 0%,100% { transform: scale(1); } 50% { transform: scale(1.2); } }
.award span { font-size: 0.75rem; color: var(--muted-text); }
.empty { color: var(--muted-text); }
.pick-label { font-weight: 600; margin: 0.5rem 0; }
.grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; }
.pick { border: 1px solid var(--surface-border); border-radius: 10px; padding: 0; cursor: pointer; background: transparent; }
.pick img { width: 100%; border-radius: 10px; }
.upload-btn { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; width: 100%; padding: 1.25rem; border: 2px dashed var(--surface-border); border-radius: 12px; background: transparent; cursor: pointer; }
.upload-btn:hover { border-color: var(--accent, #4f46e5); }
.upload-btn i { font-size: 1.75rem; color: var(--accent, #4f46e5); }
.upload-btn small { color: var(--muted-text); }
.hidden-input { display: none; }
</style>
