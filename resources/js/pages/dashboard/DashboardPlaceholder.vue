<script setup>
import { useRoute } from 'vue-router';
import { computed } from 'vue';
import Card from 'primevue/card';

const route = useRoute();
const name = computed(() => route.path.split('/').pop());
</script>

<template>
    <Card>
        <template #content>
            <div class="placeholder">
                <i class="pi pi-wrench"></i>
                <h2>{{ name }}</h2>
                <p>This section is coming soon.</p>
            </div>
        </template>
    </Card>
</template>

<style scoped>
.placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 3rem 1rem;
    text-align: center;
}
.placeholder i { font-size: 2rem; color: var(--muted-text); }
.placeholder h2 { margin: 0; text-transform: capitalize; }
.placeholder p { color: var(--muted-text); margin: 0; }
</style>
