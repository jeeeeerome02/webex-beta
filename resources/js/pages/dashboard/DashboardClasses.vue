<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Menu from 'primevue/menu';

const router = useRouter();
const user = ref(null);
const owned = ref([]);
const joined = ref([]);
const archived = ref([]);
const loading = ref(true);
const showForm = ref(false);
const saving = ref(false);
const editingToken = ref(null);
const copiedId = ref(null);
const error = ref('');
const view = ref(localStorage.getItem('classesView') || 'grid');
const sort = ref('all');
const coverInput = ref(null);
const uploadingCover = ref(false);
const menu = ref(null);
const menuClass = ref(null);

const isTeacher = computed(() => user.value?.role === 'teacher');
const dialogTitle = computed(() => (editingToken.value ? 'Edit Class' : 'Create Class'));

const sortOptions = [
    { label: 'All', value: 'all' },
    { label: 'Latest', value: 'latest' },
    { label: 'Oldest', value: 'oldest' },
    { label: 'Alphabetically (A-Z)', value: 'az' },
    { label: 'Alphabetically (Z-A)', value: 'za' },
    { label: 'Archived', value: 'archived' },
];

const applySort = (list) => {
    const arr = [...list];
    if (sort.value === 'latest') arr.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    else if (sort.value === 'oldest') arr.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    else if (sort.value === 'az') arr.sort((a, b) => a.name.localeCompare(b.name));
    else if (sort.value === 'za') arr.sort((a, b) => b.name.localeCompare(a.name));
    return arr;
};

const showArchived = computed(() => sort.value === 'archived');
const displayOwned = computed(() => (showArchived.value ? [] : applySort(owned.value)));
const displayJoined = computed(() => (showArchived.value ? [] : applySort(joined.value)));
const displayArchived = computed(() => applySort(archived.value));

const presets = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

const form = ref({
    name: '',
    description: '',
    course_type: '',
    theme_color: '#4f46e5',
    type: 'public',
    cover_image: '',
});

const typeOptions = [
    { label: 'Public', value: 'public' },
    { label: 'Private', value: 'private' },
];

const inviteUrl = (token) => `${window.location.origin}/class/${token}`;

const menuItems = computed(() => [
    { label: 'Copy Invite Link', icon: 'pi pi-link', command: () => copyInvite(menuClass.value) },
    { label: 'Edit', icon: 'pi pi-pencil', command: () => editClass(menuClass.value) },
    menuClass.value?.is_archived
        ? { label: 'Restore', icon: 'pi pi-undo', command: () => archiveClass(menuClass.value) }
        : { label: 'Archive', icon: 'pi pi-inbox', command: () => archiveClass(menuClass.value) },
]);

const setView = (v) => {
    view.value = v;
    localStorage.setItem('classesView', v);
};

const load = async () => {
    loading.value = true;
    try {
        const [me, classes] = await Promise.all([
            axios.get('/auth/user'),
            axios.get('/api/classrooms'),
        ]);
        user.value = me.data.user;
        owned.value = classes.data.owned;
        joined.value = classes.data.joined;
        archived.value = classes.data.archived || [];
    } catch {
        error.value = 'Failed to load classes.';
    } finally {
        loading.value = false;
    }
};

const createClass = async () => {
    saving.value = true;
    error.value = '';
    try {
        if (editingToken.value) {
            const { data } = await axios.put(`/api/classrooms/${editingToken.value}`, form.value);
            const i = owned.value.findIndex((c) => c.invite_token === editingToken.value);
            if (i !== -1) owned.value[i] = data.classroom;
        } else {
            const { data } = await axios.post('/api/classrooms', form.value);
            owned.value.unshift(data.classroom);
        }
        showForm.value = false;
        editingToken.value = null;
        form.value = { name: '', description: '', course_type: '', theme_color: '#4f46e5', type: 'public', cover_image: '' };
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not save class.';
    } finally {
        saving.value = false;
    }
};

const openCreate = () => {
    editingToken.value = null;
    form.value = { name: '', description: '', course_type: '', theme_color: '#4f46e5', type: 'public', cover_image: '' };
    showForm.value = true;
};

const editClass = (c) => {
    editingToken.value = c.invite_token;
    form.value = { name: c.name, description: c.description || '', course_type: c.course_type || '', theme_color: c.theme_color, type: c.type, cover_image: c.cover_image || '' };
    showForm.value = true;
};

const onCoverFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 25 * 1024 * 1024) { alert('Max 25MB.'); return; }
    uploadingCover.value = true;
    const fd = new FormData();
    fd.append('file', file);
    axios.post('/api/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(({ data }) => { form.value.cover_image = data.url; })
        .catch(() => alert('Upload failed.'))
        .finally(() => { uploadingCover.value = false; e.target.value = ''; });
};

const archiveClass = async (c) => {
    const restoring = c.is_archived;
    if (!confirm(restoring ? `Restore "${c.name}"?` : `Archive "${c.name}"?`)) return;
    await axios.post(`/api/classrooms/${c.invite_token}/archive`);
    await load();
};

const toggleMenu = (event, c) => {
    menuClass.value = c;
    menu.value.toggle(event);
};

const copyInvite = async (c) => {
    await navigator.clipboard.writeText(inviteUrl(c.invite_token));
    copiedId.value = c.id;
    setTimeout(() => (copiedId.value = null), 1500);
};

const openClass = (c) => router.push(`/dashboard/classes/${c.invite_token}`);

onMounted(load);
</script>

<template>
    <div class="classes">
        <div class="classes-head">
            <div>
                <h2>Classes</h2>
                <p>{{ isTeacher ? 'Create and manage your classes.' : 'Classes you have joined.' }}</p>
            </div>
            <div class="head-actions">
                <Select v-model="sort" :options="sortOptions" optionLabel="label" optionValue="value" class="sort-select" />
                <div class="view-toggle">
                    <button :class="{ on: view === 'grid' }" title="Boxes" @click="setView('grid')"><i class="pi pi-th-large"></i></button>
                    <button :class="{ on: view === 'list' }" title="List" @click="setView('list')"><i class="pi pi-bars"></i></button>
                </div>
                <Button v-if="isTeacher" label="New Class" icon="pi pi-plus" @click="openCreate" />
            </div>
        </div>

        <p v-if="loading">Loading…</p>

        <Menu ref="menu" :model="menuItems" popup />

        <section v-if="isTeacher && displayOwned.length" :class="view === 'grid' ? 'grid' : 'list'">
            <Card v-for="c in displayOwned" :key="c.id" class="class-card" :style="{ '--accent': c.theme_color }" @click="openClass(c)">
                <template #content>
                    <div v-if="c.cover_image" class="cover-img" :style="{ backgroundImage: `url(${c.cover_image})` }"></div>
                    <div v-else class="bar"></div>
                    <div class="card-top">
                        <div class="title-wrap">
                            <h3>{{ c.name }}</h3>
                            <span class="type">{{ c.type }}<template v-if="c.course_type"> · {{ c.course_type }}</template></span>
                        </div>
                        <Button icon="pi pi-ellipsis-v" text rounded size="small" @click.stop="toggleMenu($event, c)" />
                    </div>
                    <p class="desc">{{ c.description || 'No description' }}</p>
                    <span class="count"><i class="pi pi-users"></i> {{ c.members_count }} members</span>
                </template>
            </Card>
        </section>

        <section v-if="displayJoined.length" :class="view === 'grid' ? 'grid' : 'list'">
            <Card v-for="c in displayJoined" :key="c.id" class="class-card" :style="{ '--accent': c.theme_color }" @click="openClass(c)">
                <template #content>
                    <div v-if="c.cover_image" class="cover-img" :style="{ backgroundImage: `url(${c.cover_image})` }"></div>
                    <div v-else class="bar"></div>
                    <div class="card-top">
                        <div class="title-wrap">
                            <h3>{{ c.name }}</h3>
                            <span class="type">{{ c.type }}<template v-if="c.course_type"> · {{ c.course_type }}</template></span>
                        </div>
                    </div>
                    <p class="desc">{{ c.description || 'No description' }}</p>
                    <span class="count"><i class="pi pi-users"></i> {{ c.members_count }} members</span>
                </template>
            </Card>
        </section>

        <section v-if="showArchived">
            <h3 class="arch-title">Archived classes</h3>
            <div v-if="displayArchived.length" :class="view === 'grid' ? 'grid' : 'list'">
                <Card v-for="c in displayArchived" :key="c.id" class="class-card archived" :style="{ '--accent': c.theme_color }">
                    <template #content>
                        <div v-if="c.cover_image" class="cover-img" :style="{ backgroundImage: `url(${c.cover_image})` }"></div>
                        <div v-else class="bar"></div>
                        <div class="card-top">
                            <div class="title-wrap">
                                <h3>{{ c.name }}</h3>
                                <span class="type">{{ c.type }}<template v-if="c.course_type"> · {{ c.course_type }}</template></span>
                            </div>
                            <Button icon="pi pi-ellipsis-v" text rounded size="small" @click.stop="toggleMenu($event, c)" />
                        </div>
                        <p class="desc">{{ c.description || 'No description' }}</p>
                        <Button label="Restore" icon="pi pi-undo" size="small" outlined @click="archiveClass(c)" />
                    </template>
                </Card>
            </div>
            <p v-else class="empty">No archived classes.</p>
        </section>

        <p v-if="!loading && !showArchived && !displayOwned.length && !displayJoined.length" class="empty">
            {{ isTeacher ? 'No classes yet. Create your first class.' : 'You have not joined any classes yet.' }}
        </p>

        <Dialog v-model:visible="showForm" modal :header="dialogTitle" :style="{ width: '460px' }">
            <div class="form">
                <label>Name<InputText v-model="form.name" placeholder="e.g. Math 101" /></label>
                <label>Course Type<InputText v-model="form.course_type" placeholder="e.g. Lecture, Lab, Seminar" /></label>
                <label>Description<Textarea v-model="form.description" rows="3" autoResize /></label>
                <label>Type<Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" /></label>
                <div class="colors">
                    <span>Theme color</span>
                    <div class="swatches">
                        <button v-for="p in presets" :key="p" class="swatch" :class="{ on: form.theme_color === p }"
                            :style="{ background: p }" @click="form.theme_color = p"></button>
                    </div>
                </div>
                <div class="colors">
                    <span>Cover image (optional)</span>
                    <div class="cover-row">
                        <div class="cover-preview" :style="form.cover_image ? { backgroundImage: `url(${form.cover_image})` } : { background: form.theme_color }"></div>
                        <div class="cover-actions">
                            <Button :label="uploadingCover ? 'Uploading…' : 'Upload image'" icon="pi pi-image" size="small" outlined :loading="uploadingCover" @click="coverInput.click()" />
                            <Button v-if="form.cover_image" label="Remove" icon="pi pi-times" size="small" text severity="danger" @click="form.cover_image = ''" />
                        </div>
                        <input ref="coverInput" type="file" accept="image/*" class="hidden-input" @change="onCoverFile" />
                    </div>
                </div>
                <p v-if="error" class="err">{{ error }}</p>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="showForm = false" />
                <Button :label="editingToken ? 'Save' : 'Create'" :loading="saving" :disabled="!form.name" @click="createClass" />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
.classes-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; }
.classes-head h2 { margin: 0; font-size: 1.5rem; }
.classes-head p { margin: 0.25rem 0 0; color: var(--muted-text); }
.head-actions { display: flex; gap: 0.75rem; align-items: center; }
.view-toggle { display: flex; border: 1px solid var(--surface-border); border-radius: 8px; overflow: hidden; }
.view-toggle button { border: none; background: transparent; padding: 0.5rem 0.7rem; cursor: pointer; color: var(--muted-text); }
.view-toggle button.on { background: var(--button-primary-bg); color: var(--button-primary-text); }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.list { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem; }
.class-card :deep(.p-card-content) { display: flex; flex-direction: column; gap: 0.5rem; }
.list .class-card :deep(.p-card-content) { flex-direction: row; align-items: center; gap: 1rem; }
.list .bar { height: 40px; width: 6px; margin: 0; }
.list .desc { flex: 1; }
.class-card { cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; }
.class-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
.bar { height: 6px; border-radius: 6px; background: var(--accent); margin-bottom: 0.25rem; }
.cover-img { height: 90px; border-radius: 10px; background-size: cover; background-position: center; margin-bottom: 0.4rem; }
.list .cover-img { height: 40px; width: 60px; flex-shrink: 0; margin: 0; }
.arch-title { margin: 0 0 1rem; }
.class-card.archived { opacity: 0.85; }
.sort-select { min-width: 170px; }
.cover-row { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.4rem; }
.cover-preview { width: 90px; height: 54px; border-radius: 8px; background-size: cover; background-position: center; border: 1px solid var(--surface-border); flex-shrink: 0; }
.cover-actions { display: flex; flex-direction: column; gap: 0.35rem; align-items: flex-start; }
.hidden-input { display: none; }
.card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.title-wrap h3 { margin: 0; font-size: 1.1rem; }
.type { font-size: 0.75rem; color: var(--muted-text); text-transform: capitalize; }
.desc { color: var(--muted-text); margin: 0; font-size: 0.875rem; }
.count { font-size: 0.8125rem; color: var(--muted-text); }
.empty { color: var(--muted-text); }
.form { display: flex; flex-direction: column; gap: 0.9rem; }
.form label { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.875rem; font-weight: 600; }
.colors span { font-size: 0.875rem; font-weight: 600; }
.swatches { display: flex; gap: 0.5rem; margin-top: 0.4rem; }
.swatch { width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; }
.swatch.on { border-color: var(--page-text); }
.err { color: #ef4444; font-size: 0.85rem; }
</style>
