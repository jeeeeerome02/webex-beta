<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Dialog from 'primevue/dialog';

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
            <h3>Messages</h3>
            <p v-if="!conversations.length" class="empty">No conversations yet. Message someone from their profile.</p>
            <button v-for="c in conversations" :key="c.id" class="conv" :class="{ active: active && active.id === c.id }" @click="openWith(c.user_id)">
                <Avatar :image="c.avatar_url || undefined" :label="c.name.charAt(0).toUpperCase()" shape="circle" />
                <div class="conv-info"><strong>{{ c.name }}</strong><span>{{ c.last || 'Start chatting' }}</span></div>
            </button>
        </aside>

        <section v-if="active" class="conv-view">
            <header class="conv-head">
                <Avatar :image="active.avatar_url || undefined" :label="active.name.charAt(0).toUpperCase()" shape="circle" />
                <strong>{{ active.name }}</strong>
                <span class="spacer"></span>
                <Button icon="pi pi-pencil" text rounded size="small" @click="nick = active.name; showNick = true" />
                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteConv" />
            </header>
            <div class="chat-log" ref="chatLog">
                <div v-for="m in messages" :key="m.id" class="chat-msg" :class="{ mine: m.is_mine }">
                    <Avatar v-if="!m.is_mine" :image="m.avatar_url || undefined" shape="circle" size="small" />
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
    </div>
</template>

<style scoped>
.chat-page { display: grid; grid-template-columns: 280px 1fr; gap: 1rem; height: calc(100vh - 160px); }
.conv-list { border: 1px solid var(--surface-border); border-radius: 12px; padding: 0.75rem; overflow-y: auto; }
.conv-list h3 { margin: 0 0 0.5rem; }
.conv { width: 100%; display: flex; gap: 0.6rem; align-items: center; padding: 0.6rem; border: none; background: transparent; border-radius: 10px; cursor: pointer; text-align: left; }
.conv:hover, .conv.active { background: var(--surface-border); }
.conv-info { flex: 1; overflow: hidden; }
.conv-info strong { display: block; }
.conv-info span { font-size: 0.75rem; color: var(--muted-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
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
