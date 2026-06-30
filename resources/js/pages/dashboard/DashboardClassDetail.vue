<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';import { useRoute, useRouter } from 'vue-router';
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
import UserAvatar from '../../components/common/UserAvatar.vue';

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
const openComments = ref({});
const replyDraft = ref({});
const replyOpen = ref({});
const messages = ref([]);
const chatDraft = ref('');
const chatLog = ref(null);
const postMenu = ref(null);
const activePost = ref(null);
const memberMenu = ref(null);
const activeMember = ref(null);
const showCoTeacherAlert = ref(false);
const showLeaveConfirm = ref(false);
const showAward = ref(false);
const awardForm = ref({ label: '', icon: 'pi pi-star' });
const awardIcons = [
    'pi pi-star', 'pi pi-star-fill', 'pi pi-trophy', 'pi pi-crown', 'pi pi-thumbs-up', 'pi pi-thumbs-up-fill',
    'pi pi-heart', 'pi pi-heart-fill', 'pi pi-bolt', 'pi pi-gift', 'pi pi-flag', 'pi pi-flag-fill',
    'pi pi-sun', 'pi pi-moon', 'pi pi-shield', 'pi pi-verified', 'pi pi-check-circle', 'pi pi-book',
    'pi pi-pencil', 'pi pi-graduation-cap', 'pi pi-palette', 'pi pi-camera', 'pi pi-megaphone', 'pi pi-sparkles',
];

// Modules
const modules = ref([]);
const canManageModules = ref(false);
const showModule = ref(false);
const savingModule = ref(false);
const uploadingModule = ref(false);
const moduleForm = ref({ description: '', file_url: '', file_name: '', file_mime: '', file_size: 0 });
const moduleInput = ref(null);

// Posts sort
const postSort = ref('all');
const postSortOptions = [
    { label: 'All', value: 'all' },
    { label: 'Posts', value: 'post' },
    { label: 'Awards', value: 'award' },
    { label: 'Modules', value: 'module' },
    { label: 'Tasks', value: 'task' },
];
const filteredPosts = computed(() => (postSort.value === 'all' ? posts.value : posts.value.filter((p) => (p.kind || 'post') === postSort.value)));

const accent = computed(() => cls.value?.theme_color || '#4f46e5');
const isOwner = computed(() => cls.value?.is_owner);
const isCoTeacher = computed(() => members.value.some((m) => m.is_self && m.is_co_teacher));
const canPin = computed(() => isOwner.value || isCoTeacher.value);
const canPost = computed(() => isOwner.value || cls.value?.allow_posts);
const approvedMembers = computed(() => members.value.filter((m) => m.status === 'approved'));
const pendingMembers = computed(() => members.value.filter((m) => m.status === 'pending'));
const memberSearch = ref('');
const filteredMembers = computed(() => {
    const q = memberSearch.value.trim().toLowerCase();
    if (!q) return approvedMembers.value;
    return approvedMembers.value.filter((m) => m.name.toLowerCase().includes(q) || (m.email || '').toLowerCase().includes(q));
});

const postMenuItems = computed(() => {
    const items = [];
    if (canPin.value) items.push({ label: activePost.value?.is_pinned ? 'Unpin post' : 'Pin post', icon: 'pi pi-thumbtack', command: () => pinPost(activePost.value) });
    items.push({ label: activePost.value?.comments_enabled ? 'Turn off comments' : 'Turn on comments', icon: 'pi pi-comment', command: () => toggleComments(activePost.value) });
    items.push({ label: 'Delete post', icon: 'pi pi-trash', command: () => deletePost(activePost.value) });
    return items;
});

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
            cover_image: data.classroom.cover_image || '',
        };
        loadPosts();
        loadMessages();
        loadModules();
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

let quillRef = null;
const onEditorLoad = ({ instance }) => {
    quillRef = instance;
    const toolbar = instance.getModule('toolbar');
    toolbar.addHandler('image', () => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;
            if (file.size > 25 * 1024 * 1024) { alert('Image must be 25MB or smaller.'); return; }
            try {
                const fd = new FormData();
                fd.append('file', file);
                const { data } = await axios.post('/api/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                const range = quillRef.getSelection(true);
                quillRef.insertEmbed(range.index, 'image', data.url);
                quillRef.setSelection(range.index + 1);
            } catch { alert('Image upload failed.'); }
        };
        input.click();
    });
};

const busy = ref({});

const like = async (p) => {
    if (busy.value['lp' + p.id]) return;
    busy.value['lp' + p.id] = true;
    try {
        const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/like`);
        p.liked = data.liked; p.likes_count = data.count;
    } finally { busy.value['lp' + p.id] = false; }
};

const addComment = async (p) => {
    const body = (commentDraft.value[p.id] || '').trim();
    if (!body) return;
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/comments`, { body });
    p.comments.push(data.comment);
    commentDraft.value[p.id] = '';
};

const toggleView = (p) => { openComments.value[p.id] = !openComments.value[p.id]; };

const toggleReply = (c) => { replyOpen.value[c.id] = !replyOpen.value[c.id]; };

const addReply = async (p, c) => {
    const body = (replyDraft.value[c.id] || '').trim();
    if (!body) return;
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/comments`, { body, parent_id: c.id });
    if (!c.replies) c.replies = [];
    c.replies.push(data.comment);
    replyDraft.value[c.id] = '';
    replyOpen.value[c.id] = false;
};

const likeComment = async (p, c) => {
    if (busy.value['lc' + c.id]) return;
    busy.value['lc' + c.id] = true;
    try {
        const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/comments/${c.id}/like`);
        c.liked = data.liked; c.likes_count = data.count;
    } finally { busy.value['lc' + c.id] = false; }
};

const loadMessages = async () => {
    try {
        const { data } = await axios.get(`/api/classrooms/${token}/messages`);
        messages.value = data.messages;
        nextTick(() => { if (chatLog.value) chatLog.value.scrollTop = chatLog.value.scrollHeight; });
    } catch { messages.value = []; }
};

const sendMessage = async () => {
    if (!chatDraft.value.trim()) return;
    const { data } = await axios.post(`/api/classrooms/${token}/messages`, { body: chatDraft.value });
    messages.value.push(data.message);
    chatDraft.value = '';
    nextTick(() => { if (chatLog.value) chatLog.value.scrollTop = chatLog.value.scrollHeight; });
};

const toggleComments = async (p) => {
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/toggle-comments`);
    p.comments_enabled = data.comments_enabled;
};

const hidePost = async (p) => {
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/hide`);
    p.is_hidden = data.is_hidden;
};

const deletePost = async (p) => {
    if (!confirm('Delete this post? This cannot be undone.')) return;
    await axios.delete(`/api/classrooms/${token}/posts/${p.id}`);
    posts.value = posts.value.filter((x) => x.id !== p.id);
};

const pinPost = async (p) => {
    const { data } = await axios.post(`/api/classrooms/${token}/posts/${p.id}/pin`);
    p.is_pinned = data.is_pinned;
    loadPosts();
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
    loadPosts();
};

const removeMember = async (m) => {
    if (!confirm(`Remove ${m.name}?`)) return;
    await axios.delete(`/api/classrooms/${token}/members/${m.id}`);
    members.value = members.value.filter((x) => x.id !== m.id);
};

const leaveClass = async () => {
    showLeaveConfirm.value = false;
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

const openProfile = (m) => router.push(`/dashboard/users/${m.profile_token || m.id}`);

const addFriend = async (m) => {
    if (busy.value['f' + m.id]) return;
    busy.value['f' + m.id] = true;
    try {
        const { data } = await axios.post(`/api/users/${m.id}/friend`);
        m.friend_status = data.status;
    } finally { busy.value['f' + m.id] = false; }
};

const cancelFriend = async (m) => {
    if (busy.value['f' + m.id]) return;
    busy.value['f' + m.id] = true;
    try {
        await axios.post(`/api/users/${m.id}/friend/cancel`);
        m.friend_status = 'none';
    } finally { busy.value['f' + m.id] = false; }
};

const acceptFriend = async (m) => {
    if (busy.value['f' + m.id]) return;
    busy.value['f' + m.id] = true;
    try {
        const { data } = await axios.post(`/api/users/${m.id}/friend/accept`);
        m.friend_status = data.status;
    } finally { busy.value['f' + m.id] = false; }
};

const declineFriend = async (m) => {
    if (busy.value['f' + m.id]) return;
    busy.value['f' + m.id] = true;
    try {
        await axios.post(`/api/users/${m.id}/friend/decline`);
        m.friend_status = 'none';
    } finally { busy.value['f' + m.id] = false; }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        const { data } = await axios.put(`/api/classrooms/${token}`, settings.value);
        cls.value = data.classroom;
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not save.';
    } finally { saving.value = false; }
};

const archiveGroup = async () => {
    if (!confirm('Archive this group? Members will lose access until you restore it.')) return;
    await axios.post(`/api/classrooms/${token}/archive`);
    router.push('/dashboard/classes');
};

const settingsCoverInput = ref(null);
const uploadingSettingsCover = ref(false);
const onSettingsCover = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 25 * 1024 * 1024) { alert('Max 25MB.'); return; }
    uploadingSettingsCover.value = true;
    const fd = new FormData();
    fd.append('file', file);
    axios.post('/api/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(({ data }) => { settings.value.cover_image = data.url; })
        .catch(() => alert('Upload failed.'))
        .finally(() => { uploadingSettingsCover.value = false; e.target.value = ''; });
};

// Module helpers
const loadModules = async () => {
    try {
        const { data } = await axios.get(`/api/classrooms/${token}/modules`);
        modules.value = data.modules;
        canManageModules.value = data.can_manage;
    } catch { modules.value = []; }
};

const openModule = () => {
    moduleForm.value = { description: '', file_url: '', file_name: '', file_mime: '', file_size: 0 };
    showModule.value = true;
};

const onModuleFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 25 * 1024 * 1024) { alert('Max 25MB.'); return; }
    uploadingModule.value = true;
    const fd = new FormData();
    fd.append('file', file);
    axios.post('/api/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(({ data }) => {
            moduleForm.value.file_url = data.url;
            moduleForm.value.file_name = data.name;
            moduleForm.value.file_mime = data.mime;
            moduleForm.value.file_size = data.size;
        })
        .catch(() => alert('Upload failed.'))
        .finally(() => { uploadingModule.value = false; e.target.value = ''; });
};

const saveModule = async () => {
    if (!moduleForm.value.description.trim() || savingModule.value) return;
    savingModule.value = true;
    try {
        const { data } = await axios.post(`/api/classrooms/${token}/modules`, moduleForm.value);
        modules.value.unshift(data.module);
        showModule.value = false;
        loadPosts();
    } finally { savingModule.value = false; }
};

const archiveModule = async (m) => {
    const { data } = await axios.post(`/api/classrooms/${token}/modules/${m.id}/archive`);
    m.is_archived = data.archived;
};

const deleteModule = async (m) => {
    if (!confirm('Delete this module?')) return;
    await axios.delete(`/api/classrooms/${token}/modules/${m.id}`);
    modules.value = modules.value.filter((x) => x.id !== m.id);
};

const isImageFile = (mime) => (mime || '').startsWith('image/');
const isPdfFile = (mime) => (mime || '') === 'application/pdf';
const prettySize = (bytes) => {
    if (!bytes) return '';
    const kb = bytes / 1024;
    return kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.round(kb) + ' KB';
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
                                    <Editor v-model="newPost" editorStyle="height: 120px" placeholder="Share something with the class…" @load="onEditorLoad" />
                                    <div class="composer-foot">
                                        <Button label="Post" icon="pi pi-send" size="small" :loading="posting" :disabled="!newPost" @click="createPost" />
                                    </div>
                                </template>
                            </Card>

                            <div class="feed-bar">
                                <h4 class="feed-label">Posts</h4>
                                <Select v-model="postSort" :options="postSortOptions" optionLabel="label" optionValue="value" class="post-sort" />
                            </div>

                            <Card v-for="p in filteredPosts" :key="p.id" class="post" :class="{ hidden: p.is_hidden }">
                                <template #content>
                                    <div class="post-head">
                                        <UserAvatar :src="p.avatar_url" :name="p.author" :size="40" />
                                        <div class="post-by"><strong>{{ p.author }}</strong><span>{{ p.created_at }}</span></div>
                                        <Tag v-if="p.is_pinned" value="pinned" icon="pi pi-thumbtack" />
                                        <Tag v-if="p.is_hidden" value="hidden" severity="secondary" />
                                        <Button v-if="p.is_mine || isOwner || canPin" icon="pi pi-ellipsis-h" text rounded size="small" @click="openPostMenu($event, p)" />
                                    </div>
                                    <p class="post-body" v-html="p.body"></p>
                                    <div class="post-actions">
                                        <button class="act" :class="{ on: p.liked }" @click="like(p)">
                                            <i class="pi pi-thumbs-up"></i> {{ p.likes_count }}
                                        </button>
                                        <button v-if="p.comments_enabled" class="act" @click="toggleView(p)">
                                            <i class="pi pi-comment"></i> {{ openComments[p.id] ? 'Hide' : 'View' }} comments ({{ p.comments.length }})
                                        </button>
                                        <span v-else class="act off">Comments off</span>
                                    </div>
                                    <div v-if="p.comments_enabled && openComments[p.id]" class="comments">
                                        <div v-for="c in p.comments" :key="c.id" class="comment">
                                            <UserAvatar :src="c.avatar_url" :name="c.author" :size="32" />
                                            <div class="c-body">
                                                <div class="c-bubble"><strong>{{ c.author }}</strong> {{ c.body }}</div>
                                                <div class="c-meta">
                                                    <button class="c-act" :class="{ on: c.liked }" @click="likeComment(p, c)"><i class="pi pi-thumbs-up"></i> {{ c.likes_count }}</button>
                                                    <button class="c-act" @click="toggleReply(c)">Reply</button>
                                                </div>
                                                <div v-for="r in c.replies" :key="r.id" class="comment reply">
                                                    <UserAvatar :src="r.avatar_url" :name="r.author" :size="32" />
                                                    <div class="c-body">
                                                        <div class="c-bubble"><strong>{{ r.author }}</strong> {{ r.body }}</div>
                                                        <div class="c-meta">
                                                            <button class="c-act" :class="{ on: r.liked }" @click="likeComment(p, r)"><i class="pi pi-thumbs-up"></i> {{ r.likes_count }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="replyOpen[c.id]" class="comment-input reply">
                                                    <InputText v-model="replyDraft[c.id]" placeholder="Write a reply…" @keyup.enter="addReply(p, c)" />
                                                    <Button icon="pi pi-send" text size="small" @click="addReply(p, c)" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment-input">
                                            <InputText v-model="commentDraft[p.id]" placeholder="Write a comment…" @keyup.enter="addComment(p)" />
                                            <Button icon="pi pi-send" text size="small" @click="addComment(p)" />
                                        </div>
                                    </div>
                                </template>
                            </Card>
                            <p v-if="!filteredPosts.length" class="empty">No posts yet.</p>
                        </div>
                    </TabPanel>

                    <TabPanel value="1">
                        <div class="members">
                            <span class="search-box">
                                <i class="pi pi-search"></i>
                                <InputText v-model="memberSearch" placeholder="Search members…" />
                            </span>
                            <template v-if="isOwner && pendingMembers.length">
                                <h4>Pending approval</h4>
                                <div v-for="m in pendingMembers" :key="m.id" class="member">
                                    <UserAvatar :src="m.avatar_url" :name="m.name" :size="40" />
                                    <div class="m-info"><strong>{{ m.name }}</strong><span>{{ m.email }}</span></div>
                                    <Button label="Approve" size="small" @click="approve(m)" />
                                </div>
                            </template>
                            <h4 v-if="isOwner">Members</h4>
                            <div v-for="m in filteredMembers" :key="m.id" class="member">
                                <UserAvatar :src="m.avatar_url" :name="m.name" :size="40" style="cursor:pointer" @click="openProfile(m)" />
                                <div class="m-info clickable" @click="openProfile(m)">
                                    <strong>{{ m.name }}</strong>
                                    <span class="m-role">{{ m.is_owner ? 'teacher · owner' : (m.is_co_teacher ? 'co-teacher' : m.role) }}</span>
                                </div>
                                <Button v-if="m.is_self" label="Leave" icon="pi pi-sign-out" size="small" severity="danger" outlined @click="showLeaveConfirm = true" />
                                <template v-else>
                                    <Tag v-if="m.friend_status === 'friends'" value="Friends" icon="pi pi-check" severity="success" />
                                    <Button v-else-if="m.friend_status === 'requested'" label="Cancel" icon="pi pi-times" size="small" outlined :loading="busy['f'+m.id]" @click="cancelFriend(m)" />
                                    <span v-else-if="m.friend_status === 'incoming'" class="fr-actions">
                                        <Button label="Accept" icon="pi pi-check" size="small" :loading="busy['f'+m.id]" @click="acceptFriend(m)" />
                                        <Button label="Decline" icon="pi pi-times" size="small" outlined :loading="busy['f'+m.id]" @click="declineFriend(m)" />
                                    </span>
                                    <Button v-else label="Add friend" icon="pi pi-user-plus" size="small" outlined :loading="busy['f'+m.id]" @click="addFriend(m)" />
                                </template>
                                <Button v-if="isOwner && !m.is_self" icon="pi pi-ellipsis-v" text rounded size="small" @click="openMemberMenu($event, m)" />
                            </div>
                            <p v-if="!filteredMembers.length" class="empty">No members found.</p>
                        </div>
                    </TabPanel>

                    <TabPanel value="2">
                        <div class="modules">
                            <div class="mod-head">
                                <h4 class="feed-label">Modules</h4>
                                <Button v-if="canManageModules" label="Add New Module" icon="pi pi-plus" size="small" @click="openModule" />
                            </div>
                            <div v-for="m in modules" :key="m.id" class="mod-card" :class="{ archived: m.is_archived }">
                                <div class="mod-card-head">
                                    <UserAvatar :src="m.avatar_url" :name="m.author" :size="36" />
                                    <div class="mod-by"><strong>{{ m.author }}</strong><span>{{ m.date }}</span></div>
                                    <Tag v-if="m.is_archived" value="archived" severity="secondary" />
                                    <span v-if="canManageModules" class="mod-tools">
                                        <Button :icon="m.is_archived ? 'pi pi-undo' : 'pi pi-inbox'" text rounded size="small" :title="m.is_archived ? 'Restore' : 'Archive'" @click="archiveModule(m)" />
                                        <Button icon="pi pi-trash" text rounded size="small" severity="danger" title="Delete" @click="deleteModule(m)" />
                                    </span>
                                </div>
                                <p class="mod-desc">{{ m.description }}</p>
                                <a v-if="m.file_url" :href="m.file_url" target="_blank" class="mod-file">
                                    <img v-if="isImageFile(m.file_mime)" :src="m.file_url" :alt="m.file_name" class="mod-thumb" />
                                    <span v-else class="mod-file-icon"><i :class="isPdfFile(m.file_mime) ? 'pi pi-file-pdf' : 'pi pi-file'"></i></span>
                                    <span class="mod-file-meta"><strong>{{ m.file_name }}</strong><span>{{ prettySize(m.file_size) }}</span></span>
                                    <i class="pi pi-download"></i>
                                </a>
                            </div>
                            <p v-if="!modules.length" class="empty">No modules yet.</p>
                        </div>
                    </TabPanel>
                    <TabPanel value="3">
                        <div class="ph"><i class="pi pi-check-square"></i><p>Tasks coming soon.</p></div>
                    </TabPanel>
                    <TabPanel value="4">
                        <div class="chat">
                            <div class="chat-log" ref="chatLog">
                                <div v-for="msg in messages" :key="msg.id" class="chat-msg" :class="{ mine: msg.is_mine }">
                                    <UserAvatar v-if="!msg.is_mine" :src="msg.avatar_url" :name="msg.author" :size="32" />
                                    <div class="chat-bubble">
                                        <strong v-if="!msg.is_mine">{{ msg.author }}</strong>
                                        <p>{{ msg.body }}</p>
                                        <span class="chat-time">{{ msg.time }}</span>
                                    </div>
                                </div>
                                <p v-if="!messages.length" class="empty">No messages yet. Say hi!</p>
                            </div>
                            <div class="chat-input">
                                <InputText v-model="chatDraft" placeholder="Type a message…" @keyup.enter="sendMessage" />
                                <Button icon="pi pi-send" :disabled="!chatDraft" @click="sendMessage" />
                            </div>
                        </div>
                    </TabPanel>

                    <TabPanel v-if="isOwner" value="5">
                        <Card class="settings-card">
                            <template #content>
                                <h3>Class Settings</h3>
                                <div class="settings-grid">
                                    <div class="settings-col">
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
                                    </div>
                                    <div class="settings-col">
                                        <div class="cover-field">
                                            <span>Cover image</span>
                                            <div class="cover-preview" :style="settings.cover_image ? { backgroundImage: `url(${settings.cover_image})` } : { background: settings.theme_color }"></div>
                                            <div class="cover-actions">
                                                <Button :label="uploadingSettingsCover ? 'Uploading…' : 'Upload image'" icon="pi pi-upload" size="small" outlined :loading="uploadingSettingsCover" @click="settingsCoverInput.click()" />
                                                <Button v-if="settings.cover_image" label="Remove" icon="pi pi-times" size="small" text severity="danger" @click="settings.cover_image = ''" />
                                            </div>
                                            <input ref="settingsCoverInput" type="file" accept="image/*" class="hidden-input" @change="onSettingsCover" />
                                        </div>
                                        <div class="toggle-row"><span>Join approval</span><ToggleSwitch v-model="settings.join_approval" /></div>
                                        <div class="toggle-row"><span>Leave approval</span><ToggleSwitch v-model="settings.leave_approval" /></div>
                                        <div class="toggle-row"><span>Allow members to post</span><ToggleSwitch v-model="settings.allow_posts" /></div>
                                    </div>
                                </div>
                                <div class="settings-foot">
                                    <Button label="Save changes" icon="pi pi-check" :loading="saving" @click="saveSettings" />
                                </div>
                                <div class="danger-zone">
                                    <div class="dz-text">
                                        <strong>Archive Group</strong>
                                        <span>Members lose access until you restore it from your classes list.</span>
                                    </div>
                                    <Button label="Archive Group" icon="pi pi-inbox" severity="danger" outlined @click="archiveGroup" />
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

            <Dialog v-model:visible="showLeaveConfirm" modal header="Leave class" :style="{ width: '380px' }">
                <p>Are you sure you want to leave this class? You'll lose access to its content.</p>
                <template #footer>
                    <Button label="Cancel" text @click="showLeaveConfirm = false" />
                    <Button label="Leave" severity="danger" @click="leaveClass" />
                </template>
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

            <Dialog v-model:visible="showModule" modal header="Add New Module" :style="{ width: '520px' }">
                <div class="form">
                    <label>Description<Textarea v-model="moduleForm.description" rows="3" autoResize placeholder="What is this module about?" /></label>
                    <div class="cover-field">
                        <span>File</span>
                        <div v-if="moduleForm.file_url" class="mod-preview">
                            <img v-if="isImageFile(moduleForm.file_mime)" :src="moduleForm.file_url" alt="preview" />
                            <iframe v-else-if="isPdfFile(moduleForm.file_mime)" :src="moduleForm.file_url" title="preview"></iframe>
                            <div v-else class="mod-preview-file"><i class="pi pi-file"></i><span>{{ moduleForm.file_name }}</span></div>
                            <div class="mod-preview-meta">{{ moduleForm.file_name }} · {{ prettySize(moduleForm.file_size) }}</div>
                        </div>
                        <div class="cover-actions">
                            <Button :label="uploadingModule ? 'Uploading…' : (moduleForm.file_url ? 'Replace file' : 'Choose file')" icon="pi pi-upload" size="small" outlined :loading="uploadingModule" @click="moduleInput.click()" />
                            <Button v-if="moduleForm.file_url" label="Remove" icon="pi pi-times" size="small" text severity="danger" @click="moduleForm.file_url = ''; moduleForm.file_name = ''; moduleForm.file_mime = ''; moduleForm.file_size = 0" />
                        </div>
                        <input ref="moduleInput" type="file" class="hidden-input" @change="onModuleFile" />
                    </div>
                </div>
                <template #footer>
                    <Button label="Cancel" text @click="showModule = false" />
                    <Button label="Post Module" icon="pi pi-send" :loading="savingModule" :disabled="!moduleForm.description.trim() || uploadingModule" @click="saveModule" />
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
.post-body :deep(img) { max-width: 100%; height: auto; border-radius: 8px; margin: 0.5rem 0; }
.post-actions { display: flex; gap: 1rem; border-top: 1px solid var(--surface-border); padding-top: 0.5rem; }
.act { background: none; border: none; cursor: pointer; color: var(--muted-text); display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; }
.act.on { color: var(--accent); font-weight: 600; }
.post-body :deep(.pi-star), .post-body :deep(.pi-trophy), .post-body :deep(.pi-crown), .post-body :deep(.pi-bolt) {
    color: #f59e0b; animation: shine 2s ease-in-out infinite; filter: drop-shadow(0 0 6px rgba(245,158,11,0.7)); }
@keyframes shine { 0%, 100% { transform: scale(1); filter: drop-shadow(0 0 4px rgba(245,158,11,0.5)); } 50% { transform: scale(1.25); filter: drop-shadow(0 0 12px rgba(245,158,11,0.95)); } }
.act.off { font-style: italic; }
.comments { display: flex; flex-direction: column; gap: 0.5rem; }
.comment { display: flex; gap: 0.5rem; font-size: 0.875rem; align-items: flex-start; }
.comment.reply { margin-left: 1.5rem; }
.c-body { display: flex; flex-direction: column; gap: 0.15rem; flex: 1; }
.c-bubble { background: var(--surface-100, rgba(0,0,0,0.04)); padding: 0.4rem 0.7rem; border-radius: 12px; align-self: flex-start; }
.c-meta { display: flex; gap: 0.75rem; padding-left: 0.5rem; }
.c-act { background: none; border: none; cursor: pointer; color: var(--muted-text); font-size: 0.75rem; display: flex; align-items: center; gap: 0.25rem; }
.c-act.on { color: var(--accent); font-weight: 600; }
.comment-input { display: flex; gap: 0.4rem; }
.comment-input.reply { margin-left: 1.5rem; }
.comment-input :deep(.p-inputtext) { flex: 1; }
.chat { display: flex; flex-direction: column; height: 60vh; padding-top: 1rem; }
.chat-log { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 0.6rem; padding: 0.5rem; }
.chat-msg { display: flex; gap: 0.5rem; align-items: flex-end; }
.chat-msg.mine { flex-direction: row-reverse; }
.chat-bubble { max-width: 70%; background: var(--surface-100, rgba(0,0,0,0.05)); padding: 0.5rem 0.8rem; border-radius: 14px; }
.chat-msg.mine .chat-bubble { background: var(--accent); color: #fff; }
.chat-bubble p { margin: 0.1rem 0; }
.chat-bubble strong { font-size: 0.75rem; }
.chat-time { font-size: 0.65rem; opacity: 0.7; }
.chat-input { display: flex; gap: 0.5rem; padding-top: 0.5rem; }
.chat-input :deep(.p-inputtext) { flex: 1; }
.toggle-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; font-weight: 600; }
.t-marker { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; color: #fff; }
.members { display: flex; flex-direction: column; gap: 0.6rem; padding-top: 1rem; }
.search-box { display: flex; align-items: center; gap: 0.5rem; border: 1px solid var(--surface-border); border-radius: 10px; padding: 0 0.75rem; }
.search-box i { color: var(--muted-text); }
.search-box :deep(.p-inputtext) { border: none; box-shadow: none; flex: 1; background: transparent; }
.members h4 { margin: 0.5rem 0 0; }
.member { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border: 1px solid var(--surface-border); border-radius: 10px; }
.m-info { display: flex; flex-direction: column; flex: 1; }
.m-info span { font-size: 0.8125rem; color: var(--muted-text); }
.m-role { text-transform: capitalize; }
.ph { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 3rem; color: var(--muted-text); }
.ph i { font-size: 2rem; }
.settings-card { margin-top: 1rem; }
.form { display: flex; flex-direction: column; gap: 0.9rem; }
.form label { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.875rem; font-weight: 600; }
.colors span { font-size: 0.875rem; font-weight: 600; }
.swatches { display: flex; gap: 0.5rem; margin-top: 0.4rem; }
.swatch { width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; }
.swatch.on { border-color: var(--page-text); }
.empty { color: var(--muted-text); }
.err { color: #ef4444; }

/* Posts sort bar */
.feed-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.post-sort { min-width: 160px; }

/* Modules */
.modules { display: flex; flex-direction: column; gap: 0.9rem; padding-top: 1rem; }
.mod-head { display: flex; align-items: center; justify-content: space-between; }
.mod-card { border: 1px solid var(--surface-border); border-radius: 12px; padding: 0.9rem; display: flex; flex-direction: column; gap: 0.6rem; }
.mod-card.archived { opacity: 0.6; }
.mod-card-head { display: flex; align-items: center; gap: 0.6rem; }
.mod-by { display: flex; flex-direction: column; flex: 1; }
.mod-by span { font-size: 0.75rem; color: var(--muted-text); }
.mod-tools { display: flex; gap: 0.2rem; }
.mod-desc { margin: 0; white-space: pre-wrap; }
.mod-file { display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--surface-border); border-radius: 10px; padding: 0.5rem 0.75rem; text-decoration: none; color: inherit; }
.mod-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; }
.mod-file-icon { width: 48px; height: 48px; border-radius: 8px; display: grid; place-items: center; background: var(--surface-100, rgba(0,0,0,0.05)); font-size: 1.4rem; }
.mod-file-meta { display: flex; flex-direction: column; flex: 1; }
.mod-file-meta span { font-size: 0.75rem; color: var(--muted-text); }

/* Module preview */
.mod-preview { border: 1px solid var(--surface-border); border-radius: 10px; overflow: hidden; }
.mod-preview img { width: 100%; max-height: 240px; object-fit: contain; display: block; background: #000; }
.mod-preview iframe { width: 100%; height: 240px; border: none; }
.mod-preview-file { display: flex; align-items: center; gap: 0.5rem; padding: 1rem; font-size: 1.2rem; }
.mod-preview-file span { font-size: 0.9rem; }
.mod-preview-meta { font-size: 0.75rem; color: var(--muted-text); padding: 0.5rem 0.75rem; border-top: 1px solid var(--surface-border); }

/* Settings layout */
.settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.settings-col { display: flex; flex-direction: column; gap: 0.9rem; }
.settings-col label { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.875rem; font-weight: 600; }
.settings-foot { display: flex; justify-content: flex-end; margin-top: 1.25rem; }
.cover-field { display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem; font-weight: 600; }
.cover-preview { width: 100%; height: 110px; border-radius: 10px; background-size: cover; background-position: center; border: 1px solid var(--surface-border); }
.cover-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.hidden-input { display: none; }
.danger-zone { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1.5rem; padding: 1rem; border: 1px solid #ef4444; border-radius: 12px; background: rgba(239,68,68,0.05); }
.dz-text { display: flex; flex-direction: column; }
.dz-text span { font-size: 0.8125rem; color: var(--muted-text); font-weight: 400; }
@media (max-width: 640px) { .settings-grid { grid-template-columns: 1fr; } }
</style>
