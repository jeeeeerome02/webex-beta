<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';

const route = useRoute();
const router = useRouter();
const user = ref(null);
const awards = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/users/${route.params.id}`);
        user.value = data.user;
        awards.value = data.awards;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="profile">
        <p v-if="loading">Loading…</p>
        <template v-else-if="user">
            <Card class="head-card">
                <template #content>
                    <Avatar :label="user.name.charAt(0).toUpperCase()" size="xlarge" shape="circle" />
                    <div class="head-info">
                        <h2>{{ user.name }}</h2>
                        <p>{{ user.email }}</p>
                        <Tag :value="user.role" severity="secondary" />
                    </div>
                </template>
            </Card>

            <h3 class="sec">Awards</h3>
            <div class="awards">
                <Card v-for="a in awards" :key="a.id" class="award">
                    <template #content>
                        <i :class="a.icon"></i>
                        <strong>{{ a.label }}</strong>
                        <span>{{ a.class }} · {{ a.date }}</span>
                    </template>
                </Card>
                <p v-if="!awards.length" class="empty">No awards yet.</p>
            </div>
        </template>
    </div>
</template>

<style scoped>
.head-card :deep(.p-card-content) { display: flex; align-items: center; gap: 1.25rem; }
.head-info h2 { margin: 0; }
.head-info p { margin: 0.25rem 0; color: var(--muted-text); }
.sec { margin: 1.5rem 0 0.75rem; }
.awards { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
.award :deep(.p-card-content) { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; text-align: center; }
.award i { font-size: 1.75rem; color: var(--accent, #f59e0b); }
.award span { font-size: 0.75rem; color: var(--muted-text); }
.empty { color: var(--muted-text); }
</style>
