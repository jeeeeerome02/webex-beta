<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';

const user = ref(null);
const owned = ref([]);
const joined = ref([]);
const loading = ref(true);
const showCreate = ref(false);
const saving = ref(false);
const copiedId = ref(null);
const error = ref('');

const isTeacher = computed(() => user.value?.role === 'teacher');

const presets = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

const form = ref({
    name: '',
    description: '',
    theme_color: '#4f46e5',
    type: 'public',
});

const typeOptions = [
    { label: 'Public', value: 'public' },
    { label: 'Private', value: 'private' },
];

const inviteUrl = (token) => `${window.location.origin}/class/${token}`;

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
        const { data } = await axios.post('/api/classrooms', form.value);
        owned.value.unshift(data.classroom);
        showCreate.value = false;
        form.value = { name: '', description: '', theme_color: '#4f46e5', type: 'public' };
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not create class.';
    } finally {
        saving.value = false;
    }
};

const copyInvite = async (c) => {
    await navigator.clipboard.writeText(inviteUrl(c.invite_token));
    copiedId.value = c.id;
    setTimeout(() => (copiedId.value = null), 1500);
};

onMounted(load);
</script>

<template>
    <div class="classes">
        <div class="classes-head">
            <div>
                <h2>Classes</h2>
                <p>{{ isTeacher ? 'Create and manage your classes.' : 'Classes you have joined.' }}</p>
            </div>
            <Button v-if="isTeacher" label="New Class" icon="pi pi-plus" @click="showCreate = true" />
        </div>

        <p v-if="loading">Loading…</p>

        <section v-if="isTeacher && owned.length" class="grid">
            <Card v-for="c in owned" :key="c.id" class="class-card" :style="{ '--accent': c.theme_color }">
                <template #content>
                    <div class="bar"></div>
                    <div class="card-top">
                        <h3>{{ c.name }}</h3>
                        <Tag :value="c.type" :severity="c.type === 'public' ? 'success' : 'warn'" />
                    </div>
                    <p class="desc">{{ c.description || 'No description' }}</p>
                    <span class="count"><i class="pi pi-users"></i> {{ c.members_count }} members</span>
                    <Button
                        :label="copiedId === c.id ? 'Copied!' : 'Copy invite link'"
                        :icon="copiedId === c.id ? 'pi pi-check' : 'pi pi-link'"
                        size="small" text @click="copyInvite(c)"
                    />
                </template>
            </Card>
        </section>

        <section v-if="joined.length" class="grid">
            <Card v-for="c in joined" :key="c.id" class="class-card" :style="{ '--accent': c.theme_color }">
                <template #content>
                    <div class="bar"></div>
                    <div class="card-top">
                        <h3>{{ c.name }}</h3>
                        <Tag :value="c.type" :severity="c.type === 'public' ? 'success' : 'warn'" />
                    </div>
                    <p class="desc">{{ c.description || 'No description' }}</p>
                    <span class="count"><i class="pi pi-users"></i> {{ c.members_count }} members</span>
                </template>
            </Card>
        </section>

        <p v-if="!loading && !owned.length && !joined.length" class="empty">
            {{ isTeacher ? 'No classes yet. Create your first class.' : 'You have not joined any classes yet.' }}
        </p>

        <Dialog v-model:visible="showCreate" modal header="Create Class" :style="{ width: '460px' }">
            <div class="form">
                <label>Name<InputText v-model="form.name" placeholder="e.g. Math 101" /></label>
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
                <Button label="Cancel" text @click="showCreate = false" />
                <Button label="Create" :loading="saving" :disabled="!form.name" @click="createClass" />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
.classes-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; }
.classes-head h2 { margin: 0; font-size: 1.5rem; }
.classes-head p { margin: 0.25rem 0 0; color: var(--muted-text); }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.class-card :deep(.p-card-content) { display: flex; flex-direction: column; gap: 0.5rem; }
.bar { height: 6px; border-radius: 6px; background: var(--accent); margin-bottom: 0.25rem; }
.card-top { display: flex; justify-content: space-between; align-items: center; }
.card-top h3 { margin: 0; font-size: 1.1rem; }
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
