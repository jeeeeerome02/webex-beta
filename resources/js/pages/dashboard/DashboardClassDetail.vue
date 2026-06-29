<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import Menu from 'primevue/menu';
import Editor from 'primevue/editor';
import Dialog from 'primevue/dialog';

const route = useRoute();
const router = useRouter();

const token = route.params.id;
const cls = ref(null);
const members = ref([]);
const posts = ref([]);
const loading = ref(true);
const error = ref('');
const activeTab = ref('0');

const presets = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];
const typeOptions = [
    { label: 'Public', value: 'public' },
    { label: 'Private', value: 'private' },
];
const saving = ref(false);
const settings = ref({});
const newPost = ref('');
const posting = ref(false);
const commentDraft = ref({});
const postMenu = ref(null);
const activePost = ref(null);
const memberMenu = ref(null);
const activeMember = ref(null);
const showCoTeacherAlert = ref(false);
const showAward = ref(false);
const awardForm = ref({ label: '', icon: 'pi pi-star' });
const awardIcons = ['pi pi-star', 'pi pi-trophy', 'pi pi-thumbs-up', 'pi pi-bolt', 'pi pi-heart', 'pi pi-crown'];

const accent = computed(() => cls.value?.theme_color || '#4f46e5');
const isOwner = computed(() => cls.value?.is_owner);
const canPost = computed(() => isOwner.value || cls.value?.allow_posts);
const approvedMembers = computed(() => members.value.filter((m) => m.status === 'approved'));
const pendingMembers = computed(() => members.value.filter((m) => m.status === 'pending'));

const postMenuItems = computed(() => [
    { label: activePost.value?.comments_enabled ? 'Turn off comments' : 'Turn on comments', icon: 'pi pi-comment', command: () => toggleComments(activePost.value) },
    { label: activePost.value?.is_hidden ? 'Unhide post' : 'Hide post', icon: 'pi pi-eye-slash', command: () => hidePost(activePost.value) },
]);

const memberMenuItems = computed(() => {
    const m = activeMember.value;
    const items = [];
    if (m?.role === 'teacher' && !m?.is_co_teacher) items.push({ label: 'Assign as Co-teacher', icon: 'pi pi-user-plus', command: () => assignCoTeacher(m) });
    items.push({ label: 'Give Award', icon: 'pi pi-trophy', command: () => openAward(m) });
    items.push({ label: 'Remove', icon: 'pi pi-trash', command: () => removeMember(m) });
    return items;
});

const load = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/classrooms/${token}`);
        cls.value = data.classroom;
        members.value = data.members;
        settings.value = {
            name: data.classroom.name, description: data.classroom.description || '',
            course_type: data.classroom.course_type || '', theme_color: data.classroom.theme_color,
            type: data.classroom.type, join_approval: data.classroom.join_approval,
            leave_approval: data.classroom.leave_approval, allow_posts: data.classroom.allow_posts,
        };
        loadPosts();
    } catch (e) {
        if (e.response?.status === 403) { router.replace(`/class/${token}`); return; }
        error.value = e.response?.data?.message || 'Could not load class.';
    } finally {
        loading.value = false;
    }
};

const loadPosts = async () => {
    try {
        const { data } = await axios.get(`/api/classrooms/${token}/posts`);
        posts.value = data.posts;
    } catch { posts.value = []; }
};

const createPost = async () => {
    if (!newPost.value) return;
    posting.value = true;
    try {
        const { data } = await axios.post(`/api/classrooms/${token}/posts`, { body: newPost.value });
        posts.value.unshift(data.post);
        newPost.value = '';
    } finally { posting.value = false; }
};

const like = async (p) => {
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/like`);
    p.liked = data.liked; p.likes_count = data.count;
};

const addComment = async (p) => {
    const body = (commentDraft.value[p.id] || '').trim();
    if (!body) return;
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/comments`, { body });
    p.comments.push(data.comment);
    commentDraft.value[p.id] = '';
};

const toggleComments = async (p) => {
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/toggle-comments`);
    p.comments_enabled = data.comments_enabled;
};

const hidePost = async (p) => {
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/hide`);
    p.is_hidden = data.is_hidden;
};

const openPostMenu = (e, p) => { activePost.value = p; postMenu.value.toggle(e); };

const approve = async (m) => {
    await axios.post(`/api/classrooms/${token}/members/${m.id}/approve`);
    m.status = 'approved';
};

const openMemberMenu = (e, m) => { activeMember.value = m; memberMenu.value.toggle(e); };

const assignCoTeacher = async (m) => {
    await axios.post(`/api/classrooms/${token}/members/${m.id}/co-teacher`);
    m.is_co_teacher = true;
};

const openAward = (m) => { activeMember.value = m; awardForm.value = { label: '', icon: 'pi pi-star' }; showAward.value = true; };

const giveAward = async () => {
    if (!awardForm.value.label) return;
    await axios.post(`/api/classrooms/${token}/members/${activeMember.value.id}/award`, awardForm.value);
    showAward.value = false;
};

const removeMember = async (m) => {
    if (!confirm(`Remove ${m.name}?`)) return;
    await axios.delete(`/api/classrooms/${token}/members/${m.id}`);
    members.value = members.value.filter((x) => x.id !== m.id);
};

const leaveClass = async () => {
    try {
        await axios.post(`/api/classrooms/${token}/leave`);
        router.push('/dashboard/classes');
    } catch (e) {
        if (e.response?.status === 422 && e.response.data.message?.includes('Co-teacher')) {
            showCoTeacherAlert.value = true;
        } else {
            alert(e.response?.data?.message || 'Could not leave.');
        }
    }
};

const openProfile = (m) => router.push(`/dashboard/users/${m.id}`);

const saveSettings = async () => {
    saving.value = true;
    try {
        const { data } = await axios.put(`/api/classrooms/${token}`, settings.value);
        cls.value = data.classroom;
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not save.';
    } finally { saving.value = false; }
};

onMounted(load);
</script>

<template>
    <div class="cd" :style="{ '--accent': accent }">
        <p v-if="loading">Loading…</p>
        <p v-else-if="error" class="err">{{ error }}</p>

        <template v-else>
            <header class="cd-hero">
                <h1>{{ cls.name }}</h1>
                <p>{{ cls.course_type }} · <span class="cap">{{ cls.type }}</span> class</p>
            </header>

            <Tabs v-model:value="activeTab">
                <TabList>
                    <Tab value="0"><i class="pi pi-clock"></i> Timeline</Tab>
                    <Tab value="1"><i class="pi pi-users"></i> Members</Tab>
                    <Tab value="2"><i class="pi pi-book"></i> Modules</Tab>
                    <Tab value="3"><i class="pi pi-check-square"></i> Tasks</Tab>
                    <Tab value="4"><i class="pi pi-comments"></i> Group Chat</Tab>
                    <Tab v-if="isOwner" value="5"><i class="pi pi-cog"></i> Settings</Tab>
                </TabList>
                <TabPanels>
                    <TabPanel value="0">
                        <div class="feed">
                            <Card v-if="canPost" class="composer">
                                <template #content>
                                    <label class="field-label">Write a Post</label>
                                    <Editor v-model="newPost" editorStyle="height: 120px" placeholder="Share something with the class…" />
                                    <div class="composer-foot">
                                        <Button label="Post" icon="pi pi-send" size="small" :loading="posting" :disabled="!newPost" @click="createPost" />
                                    </div>
                                </template>
                            </Card>

                            <h4 class="feed-label">Posts</h4>

                            <Card v-for="p in posts" :key="p.id" class="post" :class="{ hidden: p.is_hidden }">
                                <template #content>
                                    <div class="post-head">
                                        <Avatar :label="p.author.charAt(0).toUpperCase()" shape="circle" />
                                        <div class="post-by"><strong>{{ p.author }}</strong><span>{{ p.created_at }}</span></div>
                                        <Tag v-if="p.is_hidden" value="hidden" severity="secondary" />
                                        <Button v-if="p.is_mine || isOwner" icon="pi pi-ellipsis-h" text rounded size="small" @click="openPostMenu($event, p)" />
                                    </div>
                                    <p class="post-body" v-html="p.body"></p>
                                    <div class="post-actions">
                                        <button class="act" :class="{ on: p.liked }" @click="like(p)">
                                            <i class="pi pi-thumbs-up"></i> {{ p.likes_count }}
                                        </button>
                                        <span v-if="p.comments_enabled" class="act"><i class="pi pi-comment"></i> {{ p.comments.length }}</span>
                                        <span v-else class="act off">Comments off</span>
                                    </div>
                                    <div v-if="p.comments_enabled" class="comments">
                                        <div v-for="c in p.comments" :key="c.id" class="comment">
                                            <Avatar :label="c.author.charAt(0).toUpperCase()" shape="circle" size="small" />
                                            <div><strong>{{ c.author }}</strong> {{ c.body }}</div>
                                        </div>
                                        <div class="comment-input">
                                            <InputText v-model="commentDraft[p.id]" placeholder="Write a comment…" @keyup.enter="addComment(p)" />
                                            <Button icon="pi pi-send" text size="small" @click="addComment(p)" />
                                        </div>
                                    </div>
                                </template>
                            </Card>
                            <p v-if="!posts.length" class="empty">No posts yet.</p>
                        </div>
                    </TabPanel>

                    <TabPanel value="1">
                        <div class="members">
                            <template v-if="isOwner && pendingMembers.length">
                                <h4>Pending approval</h4>
                                <div v-for="m in pendingMembers" :key="m.id" class="member">
                                    <Avatar :label="m.name.charAt(0).toUpperCase()" shape="circle" />
                                    <div class="m-info"><strong>{{ m.name }}</strong><span>{{ m.email }}</span></div>
                                    <Button label="Approve" size="small" @click="approve(m)" />
                                </div>
                            </template>
                            <h4 v-if="isOwner">Members</h4>
                            <div v-for="m in approvedMembers" :key="m.id" class="member">
                                <Avatar :label="m.name.charAt(0).toUpperCase()" shape="circle" style="cursor:pointer" @click="openProfile(m)" />
                                <div class="m-info clickable" @click="openProfile(m)"><strong>{{ m.name }}</strong><span>{{ m.email }}</span></div>
                                <Tag v-if="m.is_co_teacher" value="co-teacher" severity="secondary" />
                                <Tag :value="m.role" severity="secondary" />
                                <Button v-if="isOwner" icon="pi pi-ellipsis-v" text rounded size="small" @click="openMemberMenu($event, m)" />
                            </div>
                            <p v-if="!members.length" class="empty">No members yet.</p>
                            <Button class="leave-btn" label="Leave class" icon="pi pi-sign-out" severity="danger" outlined @click="leaveClass" />
                        </div>
                    </TabPanel>

                    <TabPanel value="2">
                        <div class="ph"><i class="pi pi-book"></i><p>Modules coming soon.</p></div>
                    </TabPanel>
                    <TabPanel value="3">
                        <div class="ph"><i class="pi pi-check-square"></i><p>Tasks coming soon.</p></div>
                    </TabPanel>
                    <TabPanel value="4">
                        <div class="ph"><i class="pi pi-comments"></i><p>Group chat coming soon.</p></div>
                    </TabPanel>

                    <TabPanel v-if="isOwner" value="5">
                        <Card class="settings-card">
                            <template #content>
                                <h3>Class Settings</h3>
                                <div class="form">
                                    <label>Name<InputText v-model="settings.name" /></label>
                                    <label>Course Type<InputText v-model="settings.course_type" /></label>
                                    <label>Description<Textarea v-model="settings.description" rows="3" autoResize /></label>
                                    <label>Type<Select v-model="settings.type" :options="typeOptions" optionLabel="label" optionValue="value" /></label>
                                    <div class="colors">
                                        <span>Theme color</span>
                                        <div class="swatches">
                                            <button v-for="p in presets" :key="p" class="swatch" :class="{ on: settings.theme_color === p }"
                                                :style="{ background: p }" @click="settings.theme_color = p"></button>
                                        </div>
                                    </div>
                                    <div class="toggle-row"><span>Join approval</span><ToggleSwitch v-model="settings.join_approval" /></div>
                                    <div class="toggle-row"><span>Leave approval</span><ToggleSwitch v-model="settings.leave_approval" /></div>
                                    <div class="toggle-row"><span>Allow members to post</span><ToggleSwitch v-model="settings.allow_posts" /></div>
                                    <Button label="Save changes" :loading="saving" @click="saveSettings" />
                                </div>
                            </template>
                        </Card>
                    </TabPanel>
                </TabPanels>
            </Tabs>
            <Menu ref="postMenu" :model="postMenuItems" popup />
            <Menu ref="memberMenu" :model="memberMenuItems" popup />

            <Dialog v-model:visible="showCoTeacherAlert" modal header="Cannot leave" :style="{ width: '380px' }">
                <p>You must assign a Co-teacher before leaving the class!</p>
                <template #footer><Button label="OK" @click="showCoTeacherAlert = false" /></template>
            </Dialog>

            <Dialog v-model:visible="showAward" modal header="Give Award" :style="{ width: '380px' }">
                <div class="form">
                    <label>Award name<InputText v-model="awardForm.label" placeholder="e.g. Top Performer" /></label>
                    <div class="icon-pick">
                        <button v-for="ic in awardIcons" :key="ic" class="icon-opt" :class="{ on: awardForm.icon === ic }" @click="awardForm.icon = ic"><i :class="ic"></i></button>
                    </div>
                </div>
                <template #footer>
                    <Button label="Cancel" text @click="showAward = false" />
                    <Button label="Give" :disabled="!awardForm.label" @click="giveAward" />
                </template>
            </Dialog>
        </template>
    </div>
</template>

<style scoped>
.cd { max-width: 100%; }
.back { background: none; border: none; color: var(--muted-text); cursor: pointer; display: flex; gap: 0.4rem; align-items: center; margin-bottom: 1rem; }
.cd-hero { border-left: 5px solid var(--accent); padding: 0.25rem 0 0.25rem 1rem; margin-bottom: 1.25rem; }
.cd-hero h1 { margin: 0; font-size: 1.6rem; }
.cd-hero p { margin: 0.25rem 0 0; color: var(--muted-text); }
.cap { text-transform: capitalize; }
.feed { display: flex; flex-direction: column; gap: 1rem; padding-top: 1rem; max-width: 100%; }
.field-label, .feed-label { font-weight: 600; font-size: 0.95rem; }
.composer :deep(.p-card-content) { display: flex; flex-direction: column; gap: 0.6rem; }
.m-info.clickable { cursor: pointer; }
.leave-btn { align-self: flex-start; margin-top: 1rem; }
.icon-pick { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.icon-opt { width: 38px; height: 38px; border-radius: 8px; border: 1px solid var(--surface-border); background: transparent; cursor: pointer; font-size: 1.1rem; }
.icon-opt.on { background: var(--button-primary-bg); color: var(--button-primary-text); }
.composer :deep(.p-card-content), .post :deep(.p-card-content) { display: flex; flex-direction: column; gap: 0.6rem; }
.composer-foot { display: flex; justify-content: flex-end; }
.post.hidden { opacity: 0.6; }
.post-head { display: flex; align-items: center; gap: 0.6rem; }
.post-by { display: flex; flex-direction: column; flex: 1; }
.post-by span { font-size: 0.75rem; color: var(--muted-text); }
.post-body { margin: 0; white-space: pre-wrap; }
.post-actions { display: flex; gap: 1rem; border-top: 1px solid var(--surface-border); padding-top: 0.5rem; }
.act { background: none; border: none; cursor: pointer; color: var(--muted-text); display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; }
.act.on { color: var(--accent); font-weight: 600; }
.act.off { font-style: italic; }
.comments { display: flex; flex-direction: column; gap: 0.5rem; }
.comment { display: flex; gap: 0.5rem; font-size: 0.875rem; align-items: flex-start; }
.comment-input { display: flex; gap: 0.4rem; }
.comment-input :deep(.p-inputtext) { flex: 1; }
.toggle-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; font-weight: 600; }
.t-marker { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; color: #fff; }
.members { display: flex; flex-direction: column; gap: 0.6rem; padding-top: 1rem; }
.members h4 { margin: 0.5rem 0 0; }
.member { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border: 1px solid var(--surface-border); border-radius: 10px; }
.m-info { display: flex; flex-direction: column; flex: 1; }
.m-info span { font-size: 0.8125rem; color: var(--muted-text); }
.ph { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 3rem; color: var(--muted-text); }
.ph i { font-size: 2rem; }
.settings-card { margin-top: 1rem; max-width: 460px; }
.form { display: flex; flex-direction: column; gap: 0.9rem; }
.form label { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.875rem; font-weight: 600; }
.colors span { font-size: 0.875rem; font-weight: 600; }
.swatches { display: flex; gap: 0.5rem; margin-top: 0.4rem; }
.swatch { width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; }
.swatch.on { border-color: var(--page-text); }
.empty { color: var(--muted-text); }
.err { color: #ef4444; }
</style>
