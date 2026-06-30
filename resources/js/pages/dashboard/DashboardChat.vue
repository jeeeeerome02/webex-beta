<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Dialog from 'primevue/dialog';
import UserAvatar from '../../components/common/UserAvatar.vue';

const route = useRoute();
const router = useRouter();
const conversations = ref([]);
const active = ref(null);
const messages = ref([]);
const draft = ref('');
const sending = ref(false);
const chatLog = ref(null);
const showNick = ref(false);
const nick = ref('');
const showNew = ref(false);
const newQuery = ref('');
const newResults = ref([]);
let searchTimer = null;

watch(newQuery, (val) => {
    clearTimeout(searchTimer);
    if (!val.trim()) { newResults.value = []; return; }
    searchTimer = setTimeout(async () => {
        const { data } = await axios.get('/api/users', { params: { q: val } });
        newResults.value = data.users;
    }, 300);
});

const startChat = async (u) => {
    showNew.value = false;
    newQuery.value = '';
    newResults.value = [];
    await loadList();
    openWith(u.id);
};

const loadList = async () => {
    const { data } = await axios.get('/api/conversations');
    conversations.value = data.conversations;
};

const openById = async (id) => {
    const conv = conversations.value.find((c) => c.id === id);
    if (conv) return openWith(conv.user_id);
};

const openWith = async (userId) => {
    const { data } = await axios.get(`/api/conversations/with/${userId}`);
    active.value = data.conversation;
    messages.value = data.messages;
    const conv = conversations.value.find((c) => c.id === data.conversation.id);
    if (conv) conv.unread = 0;
    window.dispatchEvent(new CustomEvent('notif:refresh'));
    nextTick(() => { if (chatLog.value) chatLog.value.scrollTop = chatLog.value.scrollHeight; });
};

const send = async () => {
    if (!draft.value.trim() || sending.value) return;
    sending.value = true;
    try {
        const { data } = await axios.post(`/api/conversations/${active.value.id}/messages`, { body: draft.value });
        messages.value.push(data.message);
        draft.value = '';
        nextTick(() => { if (chatLog.value) chatLog.value.scrollTop = chatLog.value.scrollHeight; });
        loadList();
    } finally { sending.value = false; }
};

const saveNick = async () => {
    await axios.post(`/api/conversations/${active.value.id}/nickname`, { nickname: nick.value });
    active.value.name = nick.value || active.value.real_name;
    showNick.value = false;
    loadList();
};

const deleteConv = async () => {
    if (!confirm('Delete this conversation?')) return;
    await axios.delete(`/api/conversations/${active.value.id}`);
    active.value = null;
    messages.value = [];
    loadList();
};

onMounted(async () => {
    await loadList();
    if (route.query.c) openById(Number(route.query.c));
});
watch(() => route.query.c, (c) => { if (c) openById(Number(c)); });
</script>

<template>
    <div class="chat-page">
        <aside class="conv-list">
            <div class="conv-list-head">
                <h3>Messages</h3>
                <Button label="New chat" icon="pi pi-pencil" size="small" @click="showNew = true" />
            </div>
            <p v-if="!conversations.length" class="empty">No conversations yet. Start a new chat.</p>
            <button v-for="c in conversations" :key="c.id" class="conv" :class="{ active: active && active.id === c.id }" @click="openWith(c.user_id)">
                <UserAvatar :src="c.avatar_url" :name="c.real_name || c.name" :size="40" />
                <div class="conv-info"><strong>{{ c.name }}</strong><span>{{ c.last || 'Start chatting' }}</span></div>
                <span v-if="c.unread" class="conv-badge">{{ c.unread }}</span>
            </button>
        </aside>

        <section v-if="active" class="conv-view">
            <header class="conv-head">
                <UserAvatar :src="active.avatar_url" :name="active.real_name || active.name" :size="40" />
                <strong>{{ active.name }}</strong>
                <span class="spacer"></span>
                <Button icon="pi pi-pencil" text rounded size="small" @click="nick = active.name; showNick = true" />
                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteConv" />
            </header>
            <div class="chat-log" ref="chatLog">
                <div v-for="m in messages" :key="m.id" class="chat-msg" :class="{ mine: m.is_mine }">
                    <UserAvatar v-if="!m.is_mine" :src="m.avatar_url" :name="active.real_name || active.name" :size="30" />
                    <div class="bubble"><p>{{ m.body }}</p><span>{{ m.time }}</span></div>
                </div>
                <p v-if="!messages.length" class="empty">No messages yet. Say hi!</p>
            </div>
            <div class="chat-input">
                <InputText v-model="draft" placeholder="Type a message…" @keyup.enter="send" />
                <Button icon="pi pi-send" :loading="sending" @click="send" />
            </div>
        </section>
        <section v-else class="conv-empty"><i class="pi pi-comments"></i><p>Select a conversation</p></section>

        <Dialog v-model:visible="showNick" modal header="Set nickname" :style="{ width: '360px' }">
            <InputText v-model="nick" class="w-full" placeholder="Nickname" />
            <template #footer>
                <Button label="Save" @click="saveNick" />
            </template>
        </Dialog>

        <Dialog v-model:visible="showNew" modal header="New message" :style="{ width: '400px' }">
            <span class="new-search">
                <i class="pi pi-search"></i>
                <InputText v-model="newQuery" placeholder="Search people…" autofocus />
            </span>
            <div class="new-results">
                <button v-for="u in newResults" :key="u.id" class="new-row" @click="startChat(u)">
                    <UserAvatar :src="u.avatar_url" :name="u.name" :size="36" />
                    <div class="new-info"><strong>{{ u.name }}</strong><span>{{ u.role }}</span></div>
                </button>
                <p v-if="newQuery && !newResults.length" class="empty">No users found.</p>
            </div>
        </Dialog>
    </div>
</template>

<style scoped>
.chat-page { display: grid; grid-template-columns: 280px 1fr; gap: 1rem; height: calc(100vh - 160px); }
.conv-list { border: 1px solid var(--surface-border); border-radius: 12px; padding: 0.75rem; overflow-y: auto; }
.conv-list h3 { margin: 0 0 0.5rem; }
.conv-list-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
.conv-list-head h3 { margin: 0; }
.new-search { display: flex; align-items: center; gap: 0.5rem; border: 1px solid var(--surface-border); border-radius: 10px; padding: 0 0.75rem; margin-bottom: 0.75rem; }
.new-search :deep(.p-inputtext) { border: none; flex: 1; }
.new-results { display: flex; flex-direction: column; gap: 0.25rem; max-height: 300px; overflow-y: auto; }
.new-row { display: flex; align-items: center; gap: 0.6rem; padding: 0.5rem; border: none; background: transparent; border-radius: 8px; cursor: pointer; text-align: left; }
.new-row:hover { background: var(--surface-border); }
.new-info strong { display: block; }
.new-info span { font-size: 0.75rem; color: var(--muted-text); text-transform: capitalize; }
.conv { width: 100%; display: flex; gap: 0.6rem; align-items: center; padding: 0.6rem; border: none; background: transparent; border-radius: 10px; cursor: pointer; text-align: left; }
.conv:hover, .conv.active { background: var(--surface-border); }
.conv-info { flex: 1; overflow: hidden; }
.conv-info strong { display: block; }
.conv-info span { font-size: 0.75rem; color: var(--muted-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-badge { flex-shrink: 0; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: #1877f2; color: #fff; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.conv-view { display: flex; flex-direction: column; border: 1px solid var(--surface-border); border-radius: 12px; }
.conv-head { display: flex; align-items: center; gap: 0.6rem; padding: 0.75rem 1rem; border-bottom: 1px solid var(--surface-border); }
.spacer { flex: 1; }
.chat-log { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.6rem; }
.chat-msg { display: flex; gap: 0.5rem; align-items: flex-end; }
.chat-msg.mine { flex-direction: row-reverse; }
.bubble { background: var(--surface-border); padding: 0.5rem 0.75rem; border-radius: 12px; max-width: 70%; }
.chat-msg.mine .bubble { background: #1877f2; color: #fff; }
.bubble p { margin: 0; }
.bubble span { font-size: 0.65rem; opacity: 0.7; }
.chat-input { display: flex; gap: 0.5rem; padding: 0.75rem; border-top: 1px solid var(--surface-border); }
.chat-input :deep(.p-inputtext) { flex: 1; }
.conv-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--muted-text); border: 1px solid var(--surface-border); border-radius: 12px; }
.conv-empty i { font-size: 2.5rem; margin-bottom: 0.5rem; }
.empty { color: var(--muted-text); }
.w-full { width: 100%; }
</style>
