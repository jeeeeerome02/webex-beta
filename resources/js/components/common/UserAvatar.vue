<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    src: { type: String, default: '' },
    name: { type: String, default: 'User' },
    size: { type: [Number, String], default: 40 },
});

const failed = ref(false);
const fallback = computed(() => `https://api.dicebear.com/9.x/adventurer/svg?seed=${encodeURIComponent(props.name || 'User')}`);
const url = computed(() => (!failed.value && props.src ? props.src : fallback.value));
const px = computed(() => (typeof props.size === 'number' ? `${props.size}px` : props.size));
</script>

<template>
    <img :src="url" :style="{ width: px, height: px }" class="user-avatar" alt="avatar" @error="failed = true" />
</template>

<style scoped>
.user-avatar { border-radius: 50%; object-fit: cover; background: var(--surface-border, #e5e7eb); flex-shrink: 0; display: inline-block; }
</style>
