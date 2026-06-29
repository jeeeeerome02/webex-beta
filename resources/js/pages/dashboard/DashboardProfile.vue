<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';

const user = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/auth/user');
        user.value = data.user;
    } catch {
        user.value = null;
    }
});
</script>

<template>
    <Card class="profile-card">
        <template #title>Profile</template>
        <template #content>
            <div class="profile-row"><span>Name</span><strong>{{ user?.name || '—' }}</strong></div>
            <div class="profile-row"><span>Email</span><strong>{{ user?.email || '—' }}</strong></div>
            <div class="profile-row"><span>Role</span><strong>{{ user?.role || '—' }}</strong></div>
            <div class="profile-row"><span>Course</span><strong>{{ user?.course || '—' }}</strong></div>
        </template>
    </Card>
</template>

<style scoped>
.profile-card {
    max-width: 520px;
}
.profile-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--surface-border);
}
.profile-row:last-child { border-bottom: none; }
.profile-row span { color: var(--muted-text); text-transform: capitalize; }
</style>
