<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';

const user = ref(null);
const awards = ref([]);
const teaching = ref([]);
const showPicker = ref(false);
const target = ref('avatar_url');
const saving = ref(false);
const fileInput = ref(null);
const showEdit = ref(false);
const savingEdit = ref(false);
const editError = ref('');
const editForm = ref({ name: '', subject: '', city: '', state: '', country: '' });
const passForm = ref({ current: '', next: '', confirm: '' });

const avatarSeeds = ['Felix', 'Aneka', 'Milo', 'Zoe', 'Leo', 'Mia', 'Kai', 'Nova', 'Theo', 'Luna'];
const defaultAvatars = avatarSeeds.map((s) => `https://api.dicebear.com/9.x/adventurer/svg?seed=${s}`);
const coverSeeds = ['mountain', 'ocean', 'forest', 'city', 'desert', 'aurora', 'sunset', 'galaxy'];
const coverTemplates = coverSeeds.map((s) => `https://picsum.photos/seed/${s}/900/300`);

const isTeacher = computed(() => user.value?.role === 'teacher');
const cover = computed(() => user.value?.cover_url || '');
const avatar = computed(() => user.value?.avatar_url || `https://api.dicebear.com/9.x/adventurer/svg?seed=${user.value?.name || 'User'}`);
const locationText = computed(() => {
    const parts = [user.value?.city_municipality, user.value?.province, user.value?.country].filter(Boolean);
    return parts.join(', ');
});

const load = async () => {
    const { data: me } = await axios.get('/auth/user');
    const { data } = await axios.get(`/api/users/${me.user.id}`);
    user.value = data.user;
    awards.value = data.awards;
    teaching.value = data.teaching || [];
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

const openEdit = () => {
    editError.value = '';
    editForm.value = {
        name: user.value.name,
        subject: user.value.subject || '',
        city: user.value.city_municipality || '',
        state: user.value.province || '',
        country: user.value.country || '',
    };
    passForm.value = { current: '', next: '', confirm: '' };
    showEdit.value = true;
};

const saveProfile = async () => {
    editError.value = '';
    if (!editForm.value.name.trim() || savingEdit.value) return;
    if (passForm.value.next && passForm.value.next !== passForm.value.confirm) {
        editError.value = 'New passwords do not match.';
        return;
    }
    savingEdit.value = true;
    try {
        const payload = {
            name: editForm.value.name.trim(),
            subject: editForm.value.subject.trim(),
            city_municipality: editForm.value.city.trim(),
            province: editForm.value.state.trim(),
            country: editForm.value.country.trim(),
            avatar_url: user.value.avatar_url,
            cover_url: user.value.cover_url,
        };
        if (passForm.value.next) {
            payload.current_password = passForm.value.current;
            payload.password = passForm.value.next;
            payload.password_confirmation = passForm.value.confirm;
        }
        const { data } = await axios.put('/api/profile', payload);
        Object.assign(user.value, data.user || {});
        showEdit.value = false;
        window.dispatchEvent(new CustomEvent('profile:updated'));
    } catch (e) {
        editError.value = e.response?.data?.message || 'Could not save changes.';
    } finally { savingEdit.value = false; }
};

const toggleVisibility = async (c) => {
    const next = !c.is_public;
    try {
        await axios.post('/api/profile/class-visibility', { class_id: c.id, public: next });
        c.is_public = next;
    } catch { /* ignore */ }
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
            <div class="head-top">
                <h2>{{ user.name }}</h2>
                <Button label="Edit profile" icon="pi pi-pencil" size="small" outlined @click="openEdit" />
            </div>
            <p class="role">{{ user.role }}</p>
            <p v-if="isTeacher && user.subject" class="detail"><i class="pi pi-bookmark"></i> Teaching {{ user.subject }}</p>
            <p v-else-if="!isTeacher && user.course" class="detail"><i class="pi pi-graduation-cap"></i> Your course is {{ user.course }}</p>
            <p v-if="locationText" class="detail"><i class="pi pi-map-marker"></i> {{ locationText }}</p>
            <div class="stats">
                <span><strong>{{ user.friends_count }}</strong> friends</span>
                <span v-if="!isTeacher"><strong>{{ awards.length }}</strong> awards</span>
                <span v-else><strong>{{ teaching.length }}</strong> classes</span>
            </div>
        </div>

        <template v-if="isTeacher">
            <h3 class="sec">Teaching</h3>
            <div class="teaching">
                <div v-for="c in teaching" :key="c.id" class="t-card">
                    <i class="pi pi-book t-icon"></i>
                    <div class="t-info">
                        <strong>{{ c.name }}</strong>
                        <span>{{ c.position }}{{ c.course_type ? ' · ' + c.course_type : '' }}</span>
                    </div>
                    <Button
                        :icon="c.is_public ? 'pi pi-eye' : 'pi pi-eye-slash'"
                        :label="c.is_public ? 'Public' : 'Private'"
                        size="small"
                        text
                        :severity="c.is_public ? 'success' : 'secondary'"
                        @click="toggleVisibility(c)"
                    />
                </div>
                <p v-if="!teaching.length" class="empty">No classes yet.</p>
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

        <Dialog v-model:visible="showPicker" modal :header="target === 'cover_url' ? 'Choose a cover photo' : 'Choose a photo'" :style="{ width: '460px' }">
            <template v-if="target === 'cover_url'">
                <p class="pick-label">Pick a cover template</p>
                <div class="cover-grid">
                    <button v-for="u in coverTemplates" :key="u" class="cover-pick" @click="chooseDefault(u)">
                        <img :src="u" alt="cover" />
                    </button>
                </div>
            </template>
            <template v-else>
                <p class="pick-label">Pick a default avatar</p>
                <div class="grid">
                    <button v-for="u in defaultAvatars" :key="u" class="pick" @click="chooseDefault(u)">
                        <img :src="u" alt="avatar" />
                    </button>
                </div>
            </template>
            <p class="pick-label">Or upload your own</p>
            <button class="upload-btn" @click="fileInput.click()">
                <i class="pi pi-image"></i>
                <span>Choose an image</span>
                <small>JPG, PNG · max 25MB</small>
            </button>
            <input ref="fileInput" type="file" accept="image/*" class="hidden-input" @change="onFile" />
        </Dialog>

        <Dialog v-model:visible="showEdit" modal header="Edit profile" :style="{ width: '440px' }">
            <div class="edit-field">
                <label>Name</label>
                <InputText v-model="editForm.name" maxlength="100" placeholder="Your name" />
            </div>

            <div v-if="isTeacher" class="edit-field">
                <label>Subject you teach</label>
                <InputText v-model="editForm.subject" maxlength="100" placeholder="e.g. Mathematics" />
            </div>
            <div v-else class="edit-field">
                <label>Course</label>
                <p class="readonly">Your course is {{ user.course || 'not set' }}</p>
            </div>

            <div class="edit-row">
                <div class="edit-field">
                    <label>City</label>
                    <InputText v-model="editForm.city" maxlength="120" placeholder="City" />
                </div>
                <div class="edit-field">
                    <label>State / Province</label>
                    <InputText v-model="editForm.state" maxlength="120" placeholder="State" />
                </div>
            </div>
            <div class="edit-field">
                <label>Country</label>
                <InputText v-model="editForm.country" maxlength="120" placeholder="Country" />
            </div>

            <h4 class="edit-sub">Change password</h4>
            <div class="edit-field">
                <label>Current password</label>
                <Password v-model="passForm.current" :feedback="false" toggleMask inputClass="w-full" placeholder="Leave blank to keep" />
            </div>
            <div class="edit-row">
                <div class="edit-field">
                    <label>New password</label>
                    <Password v-model="passForm.next" :feedback="false" toggleMask inputClass="w-full" placeholder="New password" />
                </div>
                <div class="edit-field">
                    <label>Confirm</label>
                    <Password v-model="passForm.confirm" :feedback="false" toggleMask inputClass="w-full" placeholder="Confirm" />
                </div>
            </div>

            <p v-if="editError" class="edit-error">{{ editError }}</p>

            <template #footer>
                <Button label="Cancel" text @click="showEdit = false" />
                <Button label="Save" icon="pi pi-check" :loading="savingEdit" @click="saveProfile" />
            </template>
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
.head-top { display: flex; align-items: center; gap: 1rem; justify-content: space-between; padding-right: 1.5rem; }
.detail { margin: 0.2rem 0; color: var(--muted-text); font-size: 0.9rem; display: flex; align-items: center; gap: 0.45rem; }
.detail i { color: var(--accent, #4f46e5); }
.edit-field { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 1rem; }
.edit-field label { font-weight: 600; font-size: 0.85rem; }
.edit-field :deep(.p-inputtext) { width: 100%; }
.edit-field :deep(.p-password) { width: 100%; }
.edit-row { display: flex; gap: 0.75rem; }
.edit-row .edit-field { flex: 1; }
.edit-sub { margin: 0.5rem 0 0.75rem; font-size: 0.95rem; }
.readonly { margin: 0; padding: 0.5rem 0.75rem; border: 1px dashed var(--surface-border); border-radius: 8px; color: var(--muted-text); }
.edit-error { color: #ef4444; font-size: 0.85rem; margin: 0.25rem 0 0; }
.teaching { display: flex; flex-direction: column; gap: 0.6rem; }
.t-card { display: flex; align-items: center; gap: 0.85rem; background: var(--content-bg); border: 1px solid var(--surface-border); border-radius: 12px; padding: 0.85rem 1rem; }
.t-icon { font-size: 1.4rem; color: #4f46e5; }
.t-info { flex: 1; display: flex; flex-direction: column; }
.t-info span { font-size: 0.78rem; color: var(--muted-text); text-transform: capitalize; }
.cover-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-bottom: 0.75rem; }
.cover-pick { border: 1px solid var(--surface-border); border-radius: 10px; padding: 0; cursor: pointer; background: transparent; overflow: hidden; height: 70px; }
.cover-pick img { width: 100%; height: 100%; object-fit: cover; }
.cover-pick:hover { border-color: var(--accent, #4f46e5); }
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
