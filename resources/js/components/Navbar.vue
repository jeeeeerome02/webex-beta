<script setup>
    // Import the Menubar component from PrimeVue
    import Menubar from 'primevue/menubar';
    // Import the Button component for the login button
    import Button from 'primevue/button';
    //import Applogo
    import AppLogo from './AppLogo.vue';

    import { ref } from "vue";

    // Define the menu items for the Menubar
    const items = ref([
        {
            label: 'Home',
        },
        {
            label: 'Features',
        },
        {
            label: 'About Us',
        },
        {
            label: 'Contact',
        }
    ]);
</script>

<template>
    <div class="card">
    <Menubar :model="items">
        <template #start>
            <AppLogo class="app-logo" />
        </template>
        <template #end>
            <Button label="Login" icon="pi pi-user" class="mr-2" severity="info" />
            <Button label="Register" icon="pi pi-user-plus" severity="secondary" />
         </template>
    </Menubar>
    </div>
</template>



