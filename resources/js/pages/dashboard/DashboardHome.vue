<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';

const user = ref(null);

const stats = [
    { label: 'Active Exams', value: '12', icon: 'pi pi-file-edit' },
    { label: 'Students', value: '348', icon: 'pi pi-users' },
    { label: 'Avg. Score', value: '84%', icon: 'pi pi-chart-line' },
    { label: 'Pending Reviews', value: '5', icon: 'pi pi-clock' },
];

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
    <div class="overview">
        <h2 class="overview-greeting">Welcome back, {{ user?.name || 'there' }}</h2>
        <p class="overview-sub">Here's a snapshot of your activity.</p>

        <div class="overview-grid">
            <Card v-for="s in stats" :key="s.label" class="stat-card">
                <template #content>
                    <i :class="s.icon" class="stat-icon"></i>
                    <span class="stat-value">{{ s.value }}</span>
                    <span class="stat-label">{{ s.label }}</span>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.overview-greeting {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}
.overview-sub {
    color: var(--muted-text);
    margin: 0.25rem 0 1.75rem;
}
.overview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
.stat-card :deep(.p-card-content) {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    padding: 0.25rem;
}
.stat-icon {
    font-size: 1.4rem;
    color: var(--button-primary-bg);
}
.stat-value {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}
.stat-label {
    color: var(--muted-text);
    font-size: 0.875rem;
}
@media (max-width: 900px) {
    .overview-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
    .overview-grid { grid-template-columns: 1fr; }
}
</style>
