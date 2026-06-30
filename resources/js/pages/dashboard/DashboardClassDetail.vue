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
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import Menu from 'primevue/menu';
import Dialog from 'primevue/dialog';
import DatePicker from 'primevue/datepicker';
import MultiSelect from 'primevue/multiselect';
import Checkbox from 'primevue/checkbox';
import RadioButton from 'primevue/radiobutton';
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
const moduleForm = ref({ description: '', files: [] });
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
        loadTasks();
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
    const text = newPost.value.trim();
    if (!text && !postImages.value.length) return;
    posting.value = true;
    try {
        const escaped = text
            ? '<p>' + text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') + '</p>'
            : '';
        const imgs = postImages.value.map((i) => `<p><img src="${i.url}" alt="${i.name}"></p>`).join('');
        const { data } = await axios.post(`/api/classrooms/${token}/posts`, { body: escaped + imgs });
        posts.value.unshift(data.post);
        newPost.value = '';
        postImages.value = [];
    } finally { posting.value = false; }
};

const postImages = ref([]);
const uploadingPostImage = ref(false);
const postImageInput = ref(null);

const onPostImages = (e) => {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    uploadingPostImage.value = true;
    Promise.all(files.map((file) => {
        if (file.size > 25 * 1024 * 1024) { alert(`${file.name} exceeds 25MB.`); return null; }
        const fd = new FormData();
        fd.append('file', file);
        return axios.post('/api/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } }).then(({ data }) => data);
    }))
        .then((results) => { results.filter(Boolean).forEach((d) => postImages.value.push({ url: d.url, name: d.name })); })
        .catch(() => alert('Image upload failed.'))
        .finally(() => { uploadingPostImage.value = false; e.target.value = ''; });
};

const removePostImage = (i) => postImages.value.splice(i, 1);

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
    moduleForm.value = { description: '', files: [] };
    showModule.value = true;
};

const onModuleFile = (e) => {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    uploadingModule.value = true;
    Promise.all(files.map((file) => {
        if (file.size > 25 * 1024 * 1024) { alert(`${file.name} exceeds 25MB.`); return null; }
        const fd = new FormData();
        fd.append('file', file);
        return axios.post('/api/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } }).then(({ data }) => data);
    }))
        .then((results) => {
            results.filter(Boolean).forEach((d) => moduleForm.value.files.push({ url: d.url, name: d.name, mime: d.mime, size: d.size }));
        })
        .catch(() => alert('Upload failed.'))
        .finally(() => { uploadingModule.value = false; e.target.value = ''; });
};

const removeModuleFile = (i) => moduleForm.value.files.splice(i, 1);

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
const openFile = (url) => window.open(url, '_blank', 'noopener');

// Tasks
const tasks = ref([]);
const canManageTasks = ref(false);
const showTask = ref(false);
const savingTask = ref(false);
const taskStep = ref(1); // 1 = details, 2 = questions
const taskTab = ref('basic'); // basic | advanced
const blankAdvanced = () => ({ ai_check: false, allow_mobile: false, fullscreen: false, fs_exit: false, fs_shortcuts: false, camera: false, randomize: false, show_answers: false });
const blankTask = () => ({
    name: '', type: 'activity', description: '',
    deadline_type: 'none', deadline_at: null, deadline_end: null,
    duration: '', visibility: 'all', visible_members: [],
    advanced: blankAdvanced(), questions: [],
});
const taskForm = ref(blankTask());

const isComputerCourse = computed(() => /comp|program|software|\bit\b|coding|develop|inform|tech/i.test(cls.value?.course_type || ''));
const taskTypeOptions = computed(() => {
    const opts = [
        { label: 'Quiz', value: 'quiz' },
        { label: 'Study', value: 'study' },
        { label: 'Exam', value: 'exam' },
    ];
    if (isComputerCourse.value) opts.splice(1, 0, { label: 'Activity', value: 'activity' });
    return opts;
});
const deadlineOptions = [
    { label: 'No Deadline', value: 'none' },
    { label: 'This day', value: 'today' },
    { label: 'Tomorrow', value: 'tomorrow' },
    { label: 'This week', value: 'this_week' },
    { label: 'After 2 weeks', value: 'two_weeks' },
    { label: 'Custom range', value: 'custom' },
];
const durationOptions = [
    { label: '1 hour', value: '1h' },
    { label: '2 hours', value: '2h' },
    { label: '1 week', value: '1w' },
    { label: '2 weeks', value: '2w' },
];
const visibilityOptions = [
    { label: 'All', value: 'all' },
    { label: 'Specific members', value: 'specific' },
];
const questionTypeOptions = [
    { label: 'Multiple choice (Radio)', value: 'radio' },
    { label: 'Multiple choice (Checkbox)', value: 'checkbox' },
    { label: 'Essay', value: 'essay' },
    { label: 'Identification', value: 'identification' },
    { label: 'Code', value: 'code' },
];
const codeLanguageOptions = [
    { label: 'PHP', value: 'php' },
    { label: 'Python', value: 'python' },
    { label: 'JavaScript', value: 'javascript' },
    { label: 'TypeScript', value: 'typescript' },
    { label: 'Java', value: 'java' },
    { label: 'C', value: 'c' },
    { label: 'C++', value: 'cpp' },
    { label: 'C#', value: 'csharp' },
    { label: 'HTML', value: 'html' },
    { label: 'CSS', value: 'css' },
    { label: 'SQL', value: 'sql' },
    { label: 'Ruby', value: 'ruby' },
    { label: 'Go', value: 'go' },
    { label: 'Kotlin', value: 'kotlin' },
    { label: 'Swift', value: 'swift' },
    { label: 'Plain text', value: 'plaintext' },
];
const codeTemplates = {
    php: "<?php\n\nclass Solution\n{\n    public function solve()\n    {\n        // Your code here\n    }\n}\n",
    python: "class Solution:\n    def solve(self):\n        # Your code here\n        pass\n",
    javascript: "class Solution {\n  solve() {\n    // Your code here\n  }\n}\n",
    typescript: "class Solution {\n  solve(): void {\n    // Your code here\n  }\n}\n",
    java: "public class Solution {\n    public static void main(String[] args) {\n        // Your code here\n    }\n}\n",
    c: "#include <stdio.h>\n\nint main(void) {\n    // Your code here\n    return 0;\n}\n",
    cpp: "#include <iostream>\nusing namespace std;\n\nint main() {\n    // Your code here\n    return 0;\n}\n",
    csharp: "using System;\n\nclass Solution {\n    static void Main() {\n        // Your code here\n    }\n}\n",
    html: "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Document</title>\n</head>\n<body>\n    <!-- Your code here -->\n</body>\n</html>\n",
    css: "/* Your styles here */\nbody {\n}\n",
    sql: "-- Your query here\nSELECT * FROM table_name;\n",
    ruby: "class Solution\n  def solve\n    # Your code here\n  end\nend\n",
    go: "package main\n\nimport \"fmt\"\n\nfunc main() {\n    // Your code here\n}\n",
    kotlin: "fun main() {\n    // Your code here\n}\n",
    swift: "import Foundation\n\nfunc solve() {\n    // Your code here\n}\n",
    plaintext: '',
};
const applyStarter = (q) => {
    if (!q.starter || !q.starter.trim()) q.starter = codeTemplates[q.language] || '';
};
const memberOptions = computed(() => approvedMembers.value.filter((m) => !m.is_owner).map((m) => ({ label: m.name, value: m.id })));
const isExam = computed(() => taskForm.value.type === 'exam');

const loadTasks = async () => {
    try {
        const { data } = await axios.get(`/api/classrooms/${token}/tasks`);
        tasks.value = data.tasks;
        canManageTasks.value = data.can_manage;
    } catch { tasks.value = []; }
};

const openTask = () => {
    taskForm.value = blankTask();
    if (!isComputerCourse.value) taskForm.value.type = 'quiz';
    taskStep.value = 1;
    taskTab.value = 'basic';
    showTask.value = true;
};

const addQuestion = () => taskForm.value.questions.push({ name: '', type: 'radio', points: 1, options: [{ text: '', correct: false }, { text: '', correct: false }] });
const removeQuestion = (i) => taskForm.value.questions.splice(i, 1);
const addOption = (q) => q.options.push({ text: '', correct: false });
const removeOption = (q, i) => q.options.splice(i, 1);
const hasOptions = (q) => q.type === 'radio' || q.type === 'checkbox';
const markCorrect = (q, i) => {
    if (q.type === 'radio') q.options.forEach((o, idx) => { o.correct = idx === i; });
    else q.options[i].correct = !q.options[i].correct;
};
const ensureOptions = (q) => { if (hasOptions(q) && !q.options.length) q.options = [{ text: '', correct: false }, { text: '', correct: false }]; };

const saveTask = async () => {
    if (!taskForm.value.name.trim()) { taskStep.value = 1; taskTab.value = 'basic'; return; }
    savingTask.value = true;
    try {
        const payload = JSON.parse(JSON.stringify(taskForm.value));
        if (payload.visibility !== 'specific') payload.visible_members = [];
        if (payload.deadline_type !== 'custom') { payload.deadline_at = null; payload.deadline_end = null; }
        const { data } = await axios.post(`/api/classrooms/${token}/tasks`, payload);
        tasks.value.unshift(data.task);
        showTask.value = false;
        loadPosts();
    } catch (e) {
        alert(e.response?.data?.message || 'Could not save task.');
    } finally { savingTask.value = false; }
};

const archiveTask = async (t) => {
    const { data } = await axios.post(`/api/classrooms/${token}/tasks/${t.id}/archive`);
    t.is_archived = data.archived;
};

const deleteTask = async (t) => {
    if (!confirm('Delete this task?')) return;
    await axios.delete(`/api/classrooms/${token}/tasks/${t.id}`);
    tasks.value = tasks.value.filter((x) => x.id !== t.id);
};

const taskTypeMeta = {
    quiz: { icon: 'pi pi-question-circle', color: '#0ea5e9' },
    activity: { icon: 'pi pi-pencil', color: '#8b5cf6' },
    study: { icon: 'pi pi-book', color: '#10b981' },
    exam: { icon: 'pi pi-file-edit', color: '#ef4444' },
};

// ---- Exam taking (student) → dedicated page ----
const openExam = (t) => { router.push(`/dashboard/classes/${token}/task/${t.id}`); };

// ---- Submissions (teacher) ----
const showSubs = ref(false);
const subsTask = ref(null);
const subs = ref([]);
const loadingSubs = ref(false);

const openSubs = async (t) => {
    subsTask.value = t;
    subs.value = [];
    showSubs.value = true;
    loadingSubs.value = true;
    try {
        const { data } = await axios.get(`/api/classrooms/${token}/tasks/${t.id}/submissions`);
        subsTask.value = data.task;
        subs.value = data.submissions;
    } finally { loadingSubs.value = false; }
};

const logMeta = {
    exit_fullscreen: { label: 'Exited fullscreen', icon: 'pi pi-window-minimize', sev: 'warn' },
    shortcut: { label: 'Keyboard shortcut', icon: 'pi pi-bolt', sev: 'warn' },
    tab_switch: { label: 'Left tab', icon: 'pi pi-external-link', sev: 'danger' },
    page_refreshed: { label: 'Page refreshed', icon: 'pi pi-refresh', sev: 'info' },
};
const fmtTime = (iso) => { try { return new Date(iso).toLocaleTimeString(); } catch { return ''; } };
const answerText = (a) => Array.isArray(a) ? (a.length ? a.join(', ') : '—') : (a || '—');

onMounted(load);
</script>

<template>
    <div class="cd" :style="{ '--accent': accent }">
        <p v-if="loading">Loading…</p>
        <p v-else-if="error" class="err">{{ error }}</p>

        <template v-else>
            <header class="cd-hero" :class="{ 'has-cover': cls.cover_image }" :style="cls.cover_image ? { backgroundImage: `linear-gradient(90deg, rgba(0,0,0,0.85), rgba(0,0,0,0.45)), url(${cls.cover_image})` } : {}">
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
                                    <Textarea v-model="newPost" rows="3" autoResize placeholder="Share something with the class…" />
                                    <div v-if="postImages.length" class="post-thumbs">
                                        <div v-for="(img, i) in postImages" :key="i" class="post-thumb">
                                            <img :src="img.url" :alt="img.name" />
                                            <button class="thumb-x" @click="removePostImage(i)"><i class="pi pi-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="composer-foot">
                                        <Button :label="uploadingPostImage ? 'Uploading…' : 'Add image'" icon="pi pi-image" size="small" text :loading="uploadingPostImage" @click="postImageInput.click()" />
                                        <Button label="Post" icon="pi pi-send" size="small" :loading="posting" :disabled="(!newPost.trim() && !postImages.length) || uploadingPostImage" @click="createPost" />
                                        <input ref="postImageInput" type="file" accept="image/*" multiple class="hidden-input" @change="onPostImages" />
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
                                <div v-if="m.files && m.files.length" class="mod-files">
                                    <div v-for="(f, fi) in m.files" :key="fi" class="mod-file">
                                        <img v-if="isImageFile(f.mime)" :src="f.url" :alt="f.name" class="mod-thumb" />
                                        <span v-else class="mod-file-icon"><i :class="isPdfFile(f.mime) ? 'pi pi-file-pdf' : 'pi pi-file'"></i></span>
                                        <span class="mod-file-meta"><strong>{{ f.name }}</strong><span>{{ prettySize(f.size) }}</span></span>
                                        <div class="mod-file-hover">
                                            <Button label="Preview" icon="pi pi-external-link" size="small" @click="openFile(f.url)" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!modules.length" class="empty">No modules yet.</p>
                        </div>
                    </TabPanel>
                    <TabPanel value="3">
                        <div class="modules">
                            <div class="mod-head">
                                <h4 class="feed-label">Tasks</h4>
                                <Button v-if="canManageTasks" label="Add New Task" icon="pi pi-plus" size="small" @click="openTask" />
                            </div>
                            <div v-for="t in tasks" :key="t.id" class="task-card" :class="{ archived: t.is_archived }">
                                <span class="task-icon" :style="{ background: (taskTypeMeta[t.type] || {}).color }"><i :class="(taskTypeMeta[t.type] || {}).icon"></i></span>
                                <div class="task-main">
                                    <div class="task-top">
                                        <strong>{{ t.name }}</strong>
                                        <Tag :value="t.type" class="cap" />
                                        <Tag v-if="t.is_archived" value="archived" severity="secondary" />
                                    </div>
                                    <p v-if="t.description" class="task-desc">{{ t.description }}</p>
                                    <div class="task-meta">
                                        <span><i class="pi pi-clock"></i> {{ t.deadline_label }}</span>
                                        <span v-if="t.duration"><i class="pi pi-hourglass"></i> {{ t.duration }}</span>
                                        <span><i class="pi pi-list"></i> {{ t.questions_count }} question{{ t.questions_count === 1 ? '' : 's' }}</span>
                                        <span><i class="pi pi-eye"></i> {{ t.visibility === 'all' ? 'All members' : 'Specific members' }}</span>
                                    </div>
                                    <div v-if="t.advanced && (t.advanced.ai_check || t.advanced.camera || t.advanced.fullscreen || t.advanced.allow_mobile)" class="task-flags">
                                        <Tag v-if="t.advanced.ai_check" value="AI check" icon="pi pi-sparkles" severity="info" />
                                        <Tag v-if="t.advanced.camera" value="Camera" icon="pi pi-camera" severity="warn" />
                                        <Tag v-if="t.advanced.fullscreen" value="Fullscreen" icon="pi pi-window-maximize" severity="warn" />
                                        <Tag v-if="t.advanced.allow_mobile" value="Mobile OK" icon="pi pi-mobile" />
                                    </div>
                                    <div class="task-cta">
                                        <Button v-if="canManageTasks" label="Submissions" icon="pi pi-list-check" size="small" outlined @click="openSubs(t)" />
                                        <template v-else>
                                            <template v-if="t.my_status === 'submitted'">
                                                <Tag value="Submitted" icon="pi pi-check" severity="success" />
                                                <Tag v-if="t.gradable_total" :value="`AI score ${t.my_score ?? 0}%`" icon="pi pi-sparkles" severity="info" />
                                                <Button label="View result" icon="pi pi-eye" size="small" text @click="openExam(t)" />
                                            </template>
                                            <Button v-else :label="t.questions_count ? 'Answer' : 'Open'" icon="pi pi-pencil" size="small" @click="openExam(t)" />
                                        </template>
                                    </div>
                                </div>
                                <span v-if="canManageTasks" class="mod-tools">
                                    <Button :icon="t.is_archived ? 'pi pi-undo' : 'pi pi-inbox'" text rounded size="small" :title="t.is_archived ? 'Restore' : 'Archive'" @click="archiveTask(t)" />
                                    <Button icon="pi pi-trash" text rounded size="small" severity="danger" title="Delete" @click="deleteTask(t)" />
                                </span>
                            </div>
                            <p v-if="!tasks.length" class="empty">No tasks yet.</p>
                        </div>
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

            <Dialog v-model:visible="showModule" modal header="Add New Module" :style="{ width: '560px' }">
                <div class="form">
                    <label>Description<Textarea v-model="moduleForm.description" rows="3" autoResize placeholder="What is this module about?" /></label>
                    <div class="cover-field">
                        <span>Files (preview before posting)</span>
                        <div v-if="moduleForm.files.length" class="mod-preview-grid">
                            <div v-for="(f, i) in moduleForm.files" :key="i" class="mod-preview">
                                <button class="thumb-x" @click="removeModuleFile(i)"><i class="pi pi-times"></i></button>
                                <img v-if="isImageFile(f.mime)" :src="f.url" alt="preview" />
                                <iframe v-else-if="isPdfFile(f.mime)" :src="f.url" title="preview"></iframe>
                                <div v-else class="mod-preview-file"><i class="pi pi-file"></i><span>{{ f.name }}</span></div>
                                <div class="mod-preview-meta">{{ f.name }} · {{ prettySize(f.size) }}</div>
                            </div>
                        </div>
                        <div class="cover-actions">
                            <Button :label="uploadingModule ? 'Uploading…' : 'Add files'" icon="pi pi-upload" size="small" outlined :loading="uploadingModule" @click="moduleInput.click()" />
                        </div>
                        <input ref="moduleInput" type="file" multiple class="hidden-input" @change="onModuleFile" />
                    </div>
                </div>
                <template #footer>
                    <Button label="Cancel" text @click="showModule = false" />
                    <Button label="Post Module" icon="pi pi-send" :loading="savingModule" :disabled="!moduleForm.description.trim() || uploadingModule" @click="saveModule" />
                </template>
            </Dialog>

            <Dialog v-model:visible="showTask" modal :header="taskStep === 1 ? 'New Task — Details' : 'New Task — Questions'" :style="{ width: '640px' }">
                <template v-if="taskStep === 1">
                    <div class="task-tabs">
                        <button :class="{ on: taskTab === 'basic' }" @click="taskTab = 'basic'">Basic</button>
                        <button :class="{ on: taskTab === 'advanced' }" @click="taskTab = 'advanced'">Advanced</button>
                    </div>

                    <div v-show="taskTab === 'basic'" class="form">
                        <label>Name of Task<InputText v-model="taskForm.name" placeholder="e.g. Chapter 3 Quiz" /></label>
                        <label>Type of Task<Select v-model="taskForm.type" :options="taskTypeOptions" optionLabel="label" optionValue="value" /></label>
                        <label>Description<Textarea v-model="taskForm.description" rows="2" autoResize placeholder="Optional instructions…" /></label>
                        <label>Deadline<Select v-model="taskForm.deadline_type" :options="deadlineOptions" optionLabel="label" optionValue="value" /></label>
                        <div v-if="taskForm.deadline_type === 'custom'" class="task-range">
                            <label>Start<DatePicker v-model="taskForm.deadline_at" showTime hourFormat="12" /></label>
                            <label>End<DatePicker v-model="taskForm.deadline_end" showTime hourFormat="12" /></label>
                        </div>
                        <label v-if="isExam">Exam Duration / Time<Select v-model="taskForm.duration" :options="durationOptions" optionLabel="label" optionValue="value" placeholder="Select duration" /></label>
                        <label>Who can view?<Select v-model="taskForm.visibility" :options="visibilityOptions" optionLabel="label" optionValue="value" /></label>
                        <label v-if="taskForm.visibility === 'specific'">Specific members<MultiSelect v-model="taskForm.visible_members" :options="memberOptions" optionLabel="label" optionValue="value" filter placeholder="Select members" display="chip" /></label>
                    </div>

                    <div v-show="taskTab === 'advanced'" class="form">
                        <p class="adv-note">Advanced options apply mainly to exams.</p>
                        <div class="toggle-row"><span>Let AI check the exam<small>Detects similar answers and scores essays/identification.</small></span><ToggleSwitch v-model="taskForm.advanced.ai_check" /></div>
                        <div class="toggle-row"><span>Allow mobile (iOS / Android)</span><ToggleSwitch v-model="taskForm.advanced.allow_mobile" /></div>
                        <div class="toggle-row"><span>Require fullscreen</span><ToggleSwitch v-model="taskForm.advanced.fullscreen" /></div>
                        <template v-if="taskForm.advanced.fullscreen">
                            <div class="toggle-row sub"><span>Check for exiting fullscreen</span><ToggleSwitch v-model="taskForm.advanced.fs_exit" /></div>
                            <div class="toggle-row sub"><span>Check for keyboard shortcuts</span><ToggleSwitch v-model="taskForm.advanced.fs_shortcuts" /></div>
                        </template>
                        <div class="toggle-row"><span>Exam with camera open</span><ToggleSwitch v-model="taskForm.advanced.camera" /></div>
                        <div class="toggle-row"><span>Randomize question order<small>Each student gets questions in a different order.</small></span><ToggleSwitch v-model="taskForm.advanced.randomize" /></div>
                        <div class="toggle-row"><span>Show answers after submitting<small>Reveals correct answers and AI feedback once submitted.</small></span><ToggleSwitch v-model="taskForm.advanced.show_answers" /></div>
                    </div>
                </template>

                <template v-else>
                    <div class="questions">
                        <div v-for="(q, qi) in taskForm.questions" :key="qi" class="q-card">
                            <div class="q-head">
                                <strong>Question {{ qi + 1 }}</strong>
                                <Button icon="pi pi-times" text rounded size="small" severity="danger" title="Delete Question" @click="removeQuestion(qi)" />
                            </div>
                            <InputText v-model="q.name" placeholder="Question name / prompt" />
                            <Select v-model="q.type" :options="questionTypeOptions" optionLabel="label" optionValue="value" @change="ensureOptions(q)" />
                            <div class="q-points">
                                <label class="q-extra-label">Points (weight for AI scoring)</label>
                                <InputNumber v-model="q.points" :min="0" :max="1000" :step="1" showButtons placeholder="1" />
                            </div>
                            <div v-if="hasOptions(q)" class="q-options">
                                <div v-for="(o, oi) in q.options" :key="oi" class="q-option">
                                    <RadioButton v-if="q.type === 'radio'" :modelValue="o.correct" :value="true" @update:modelValue="markCorrect(q, oi)" />
                                    <Checkbox v-else :modelValue="o.correct" binary @update:modelValue="markCorrect(q, oi)" />
                                    <InputText v-model="o.text" :placeholder="`Option ${oi + 1}`" />
                                    <Button icon="pi pi-times" text rounded size="small" :disabled="q.options.length <= 1" @click="removeOption(q, oi)" />
                                </div>
                                <Button label="Add option" icon="pi pi-plus" text size="small" @click="addOption(q)" />
                            </div>
                            <div v-else-if="q.type === 'identification'" class="q-extra">
                                <label class="q-extra-label">Correct answer (for auto-grading)</label>
                                <InputText v-model="q.answer" placeholder="e.g. Photosynthesis" />
                            </div>
                            <div v-else-if="q.type === 'code'" class="q-extra">
                                <label class="q-extra-label">Programming language</label>
                                <Select v-model="q.language" :options="codeLanguageOptions" optionLabel="label" optionValue="value" placeholder="Select language" @change="applyStarter(q)" />
                                <label class="q-extra-label">Starter template (optional)</label>
                                <Textarea v-model="q.starter" rows="5" autoResize class="code-area" placeholder="Leave blank to use the built-in template" />
                                <label class="q-extra-label">Model solution (optional, helps AI scoring)</label>
                                <Textarea v-model="q.answer" rows="5" autoResize class="code-area" placeholder="Reference solution the AI compares against" />
                            </div>
                            <div v-else-if="q.type === 'essay'" class="q-extra">
                                <label class="q-extra-label">Model answer / keywords (optional, helps AI scoring)</label>
                                <Textarea v-model="q.answer" rows="3" autoResize placeholder="Reference answer the AI will compare against" />
                            </div>
                            <p v-else class="q-hint">Students will type a free-text answer.</p>
                        </div>
                        <Button label="Add New Question" icon="pi pi-plus" outlined @click="addQuestion" />
                        <p v-if="!taskForm.questions.length" class="empty">No questions yet. Add one to get started.</p>
                    </div>
                </template>

                <template #footer>
                    <Button label="Cancel" text @click="showTask = false" />
                    <Button v-if="taskStep === 2" label="Back" icon="pi pi-arrow-left" text @click="taskStep = 1" />
                    <Button v-if="taskStep === 1" label="Next: Questions" icon="pi pi-arrow-right" iconPos="right" :disabled="!taskForm.name.trim()" @click="taskStep = 2" />
                    <Button v-else label="Create Task" icon="pi pi-check" :loading="savingTask" @click="saveTask" />
                </template>
            </Dialog>

            <Dialog v-model:visible="showSubs" modal :header="`Submissions — ${subsTask?.name || ''}`" :style="{ width: '720px' }">
                <p v-if="loadingSubs">Loading…</p>
                <template v-else>
                    <p v-if="!subs.length" class="empty">No submissions yet.</p>
                    <div v-for="s in subs" :key="s.id" class="sub-card">
                        <div class="sub-head">
                            <UserAvatar :src="s.avatar_url" :name="s.student" :size="34" />
                            <div class="sub-by"><strong>{{ s.student }}</strong><span>{{ s.status === 'submitted' ? ('Submitted ' + (s.submitted_at || '')) : 'In progress' }}</span></div>
                            <Tag v-if="s.gradable_total" :value="`${s.score ?? 0}%`" icon="pi pi-sparkles" severity="success" />
                            <Tag :value="s.status" :severity="s.status === 'submitted' ? 'success' : 'secondary'" />
                        </div>

                        <div v-if="s.logs && s.logs.length" class="sub-logs">
                            <strong class="sub-logs-title"><i class="pi pi-flag"></i> Activity logs ({{ s.logs.length }})</strong>
                            <div class="sub-log" v-for="(l, li) in s.logs" :key="li">
                                <Tag :value="(logMeta[l.type] || {}).label || l.type" :icon="(logMeta[l.type] || {}).icon" :severity="(logMeta[l.type] || {}).sev || 'secondary'" />
                                <span v-if="l.detail" class="sub-log-detail">{{ l.detail }}</span>
                                <span class="sub-log-time">{{ fmtTime(l.at) }}</span>
                            </div>
                        </div>
                        <p v-else class="sub-clean"><i class="pi pi-check"></i> No flagged activity.</p>

                        <div v-if="s.flags && s.flags.length" class="sub-flags">
                            <strong class="sub-logs-title"><i class="pi pi-exclamation-triangle"></i> Possible plagiarism</strong>
                            <div v-for="(f, fi) in s.flags" :key="fi" class="sub-flag">
                                <Tag :value="`${f.percent}% similar`" severity="danger" icon="pi pi-copy" />
                                <span>Q{{ f.q + 1 }} matches <strong>{{ f.with }}</strong></span>
                            </div>
                        </div>

                        <div class="sub-answers">
                            <strong class="sub-logs-title"><i class="pi pi-list"></i> Answers</strong>
                            <div v-for="(q, qi) in (subsTask.questions || [])" :key="qi" class="sub-answer">
                                <span class="sa-q">{{ qi + 1 }}. {{ q.name || 'Question' }}</span>
                                <span class="sa-a" :class="{ code: q.type === 'code' }">{{ answerText(s.answers[qi] ?? s.answers[String(qi)]) }}</span>
                                <div v-if="q.type === 'essay' && s.ai && s.ai.essays && s.ai.essays[qi]" class="sa-ai">
                                    <i class="pi pi-sparkles"></i> AI score {{ s.ai.essays[qi].score }}/100
                                    <span class="sa-ai-fb">{{ s.ai.essays[qi].feedback }}</span>
                                </div>
                                <div v-if="q.type === 'code' && s.ai && s.ai.code && s.ai.code[qi]" class="sa-ai">
                                    <i class="pi pi-sparkles"></i> AI score {{ s.ai.code[qi].score }}/100
                                    <span class="sa-ai-fb">{{ s.ai.code[qi].feedback }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template #footer><Button label="Close" text @click="showSubs = false" /></template>
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
.cd-hero.has-cover { border-left: none; border-radius: 14px; padding: 1.75rem 1.5rem; background-size: cover; background-position: center; color: #fff; box-shadow: 0 4px 18px rgba(0,0,0,0.25); }
.cd-hero.has-cover h1 { color: #fff; font-size: 1.9rem; text-shadow: 0 1px 4px rgba(0,0,0,0.5); }
.cd-hero.has-cover p { color: rgba(255,255,255,0.85); text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
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
.mod-files { display: flex; flex-direction: column; gap: 0.5rem; }
.mod-file { display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--surface-border); border-radius: 10px; padding: 0.5rem 0.75rem; text-decoration: none; color: inherit; position: relative; }
.mod-file-hover { margin-left: auto; opacity: 0; transition: opacity 0.15s; }
.mod-file:hover .mod-file-hover { opacity: 1; }
.mod-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; }
.mod-file-icon { width: 48px; height: 48px; border-radius: 8px; display: grid; place-items: center; background: var(--surface-100, rgba(0,0,0,0.05)); font-size: 1.4rem; }
.mod-file-meta { display: flex; flex-direction: column; }
.mod-file-meta span { font-size: 0.75rem; color: var(--muted-text); }

/* Post composer thumbnails */
.post-thumbs { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.post-thumb { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid var(--surface-border); }
.post-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-x { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; border: none; background: rgba(0,0,0,0.6); color: #fff; cursor: pointer; display: grid; place-items: center; font-size: 0.7rem; z-index: 2; }
.composer-foot { display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center; }

/* Tasks */
.task-card { display: flex; gap: 0.85rem; border: 1px solid var(--surface-border); border-radius: 12px; padding: 0.9rem; align-items: flex-start; }
.task-card.archived { opacity: 0.6; }
.task-icon { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; color: #fff; flex-shrink: 0; font-size: 1.1rem; }
.task-main { flex: 1; display: flex; flex-direction: column; gap: 0.4rem; min-width: 0; }
.task-top { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.task-desc { margin: 0; color: var(--muted-text); font-size: 0.9rem; }
.task-meta { display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.8rem; color: var(--muted-text); }
.task-meta i { margin-right: 0.25rem; }
.task-flags { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.task-cta { display: flex; gap: 0.5rem; margin-top: 0.4rem; }

/* Exam taking */
.exam { display: flex; flex-direction: column; gap: 1rem; }
.exam-proctor { display: flex; gap: 0.6rem; align-items: flex-start; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.35); border-radius: 10px; padding: 0.7rem 0.85rem; font-size: 0.85rem; }
.exam-proctor i { color: #ef4444; margin-top: 0.1rem; }
.exam-desc { margin: 0; color: var(--muted-text); }
.exam-done { display: flex; gap: 0.6rem; align-items: center; background: rgba(16,185,129,0.1); border-radius: 10px; padding: 0.7rem 0.85rem; }
.exam-done i { color: #10b981; font-size: 1.3rem; }
.exam-done p { margin: 0; }
.exam-q { border: 1px solid var(--surface-border); border-radius: 10px; padding: 0.85rem; display: flex; flex-direction: column; gap: 0.6rem; }
.exam-q-head { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
.exam-opts { display: flex; flex-direction: column; gap: 0.5rem; }
.exam-opt { display: flex; align-items: center; gap: 0.6rem; cursor: pointer; }
.exam-q :deep(.code-area) { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
.exam-q :deep(.p-textarea) { width: 100%; }

/* Submissions (teacher) */
.sub-card { border: 1px solid var(--surface-border); border-radius: 12px; padding: 0.9rem; display: flex; flex-direction: column; gap: 0.7rem; margin-bottom: 0.85rem; }
.sub-head { display: flex; align-items: center; gap: 0.6rem; }
.sub-by { display: flex; flex-direction: column; flex: 1; }
.sub-by span { font-size: 0.75rem; color: var(--muted-text); }
.sub-logs, .sub-answers { display: flex; flex-direction: column; gap: 0.4rem; }
.sub-logs-title { font-size: 0.8rem; color: var(--muted-text); }
.sub-log { display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; }
.sub-log-detail { font-family: ui-monospace, monospace; color: var(--muted-text); }
.sub-log-time { margin-left: auto; font-size: 0.72rem; color: var(--muted-text); }
.sub-clean { margin: 0; font-size: 0.82rem; color: #10b981; }
.sub-answer { display: flex; flex-direction: column; gap: 0.1rem; border-left: 3px solid var(--surface-border); padding-left: 0.6rem; }
.sa-q { font-size: 0.82rem; font-weight: 600; }
.sa-a { font-size: 0.85rem; color: var(--muted-text); white-space: pre-wrap; }
.sa-a.code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; background: #f8fafc; border: 1px solid var(--surface-border); border-radius: 6px; padding: 0.4rem 0.55rem; }
.sa-ai { font-size: 0.78rem; color: #4338ca; background: #eef2ff; border-radius: 6px; padding: 0.3rem 0.5rem; margin-top: 0.25rem; }
.sa-ai-fb { display: block; color: #4f46e5; margin-top: 0.15rem; }
.sub-flags { display: flex; flex-direction: column; gap: 0.35rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.5rem 0.65rem; }
.sub-flag { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #991b1b; }
.task-tabs { display: flex; gap: 0.25rem; border-bottom: 1px solid var(--surface-border); margin-bottom: 1rem; }
.task-tabs button { background: none; border: none; padding: 0.6rem 1rem; cursor: pointer; color: var(--muted-text); font-weight: 600; border-bottom: 2px solid transparent; }
.task-tabs button.on { color: var(--accent); border-bottom-color: var(--accent); }
.task-range { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.adv-note { margin: 0 0 0.25rem; font-size: 0.8rem; color: var(--muted-text); }
.toggle-row small { display: block; font-weight: 400; font-size: 0.75rem; color: var(--muted-text); }
.toggle-row.sub { padding-left: 1rem; }
.questions { display: flex; flex-direction: column; gap: 0.85rem; }
.q-card { border: 1px solid var(--surface-border); border-radius: 10px; padding: 0.8rem; display: flex; flex-direction: column; gap: 0.5rem; }
.q-head { display: flex; align-items: center; justify-content: space-between; }
.q-options { display: flex; flex-direction: column; gap: 0.4rem; padding-left: 0.25rem; }
.q-option { display: flex; align-items: center; gap: 0.5rem; }
.q-option :deep(.p-inputtext) { flex: 1; }
.q-hint { margin: 0; font-size: 0.8rem; color: var(--muted-text); font-style: italic; }
.q-extra { display: flex; flex-direction: column; gap: 0.4rem; }
.q-points { display: flex; flex-direction: column; gap: 0.4rem; max-width: 220px; }
.q-extra-label { font-size: 0.78rem; font-weight: 600; color: var(--muted-text); }
.q-extra :deep(.code-area) { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 0.82rem; }
.mod-preview-grid { display: flex; flex-direction: column; gap: 0.6rem; }
.mod-preview { position: relative; }

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
