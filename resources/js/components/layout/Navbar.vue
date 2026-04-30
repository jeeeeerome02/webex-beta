<script setup>
import { ref } from 'vue';
import Menubar from 'primevue/menubar';
import Button from 'primevue/button';
import AppLogo from './AppLogo.vue';

const items = ref([
    { label: 'Home' },
    { label: 'Features' },
    { label: 'About Us' },
    { label: 'Contact' },
]);
</script>

<template>
    <header class="navbar">
        <Menubar :model="items">
            <template #start>
                <AppLogo />
            </template>
            <template #end>
                <Button label="Login" icon="pi pi-user" class="mr-2" severity="info" />
                <Button label="Register" icon="pi pi-user-plus" severity="secondary" />
            </template>
        </Menubar>
    </header>
</template>

<style scoped>
.navbar {
    width: 100%;
}
</style>
