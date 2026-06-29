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
const loading = ref(true);
const showForm = ref(false);
const saving = ref(false);
const editingToken = ref(null);
const copiedId = ref(null);
const error = ref('');
const view = ref(localStorage.getItem('classesView') || 'grid');
const menu = ref(null);
const menuClass = ref(null);

const isTeacher = computed(() => user.value?.role === 'teacher');
const dialogTitle = computed(() => (editingToken.value ? 'Edit Class' : 'Create Class'));

const presets = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

const form = ref({
    name: '',
    description: '',
    course_type: '',
    theme_color: '#4f46e5',
    type: 'public',
});

const typeOptions = [
    { label: 'Public', value: 'public' },
    { label: 'Private', value: 'private' },
];

const inviteUrl = (token) => `${window.location.origin}/class/${token}`;

const menuItems = computed(() => [
    { label: 'Copy Invite Link', icon: 'pi pi-link', command: () => copyInvite(menuClass.value) },
    { label: 'Edit', icon: 'pi pi-pencil', command: () => editClass(menuClass.value) },
    { label: 'Archive', icon: 'pi pi-inbox', command: () => archiveClass(menuClass.value) },
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
        form.value = { name: '', description: '', course_type: '', theme_color: '#4f46e5', type: 'public' };
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not save class.';
    } finally {
        saving.value = false;
    }
};

const openCreate = () => {
    editingToken.value = null;
    form.value = { name: '', description: '', course_type: '', theme_color: '#4f46e5', type: 'public' };
    showForm.value = true;
};

const editClass = (c) => {
    editingToken.value = c.invite_token;
    form.value = { name: c.name, description: c.description || '', course_type: c.course_type || '', theme_color: c.theme_color, type: c.type };
    showForm.value = true;
};

const archiveClass = async (c) => {
    if (!confirm(`Archive "${c.name}"?`)) return;
    await axios.post(`/api/classrooms/${c.invite_token}/archive`);
    owned.value = owned.value.filter((x) => x.invite_token !== c.invite_token);
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
                <div class="view-toggle">
                    <button :class="{ on: view === 'grid' }" title="Boxes" @click="setView('grid')"><i class="pi pi-th-large"></i></button>
                    <button :class="{ on: view === 'list' }" title="List" @click="setView('list')"><i class="pi pi-bars"></i></button>
                </div>
                <Button v-if="isTeacher" label="New Class" icon="pi pi-plus" @click="openCreate" />
            </div>
        </div>

        <p v-if="loading">Loading…</p>

        <Menu ref="menu" :model="menuItems" popup />

        <section v-if="isTeacher && owned.length" :class="view === 'grid' ? 'grid' : 'list'">
            <Card v-for="c in owned" :key="c.id" class="class-card" :style="{ '--accent': c.theme_color }" @click="openClass(c)">
                <template #content>
                    <div class="bar"></div>
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

        <section v-if="joined.length" :class="view === 'grid' ? 'grid' : 'list'">
            <Card v-for="c in joined" :key="c.id" class="class-card" :style="{ '--accent': c.theme_color }" @click="openClass(c)">
                <template #content>
                    <div class="bar"></div>
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

        <p v-if="!loading && !owned.length && !joined.length" class="empty">
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
