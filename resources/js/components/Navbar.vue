<script setup>
    // Import the Menubar component from PrimeVue
    import Menubar from 'primevue/menubar';
    // Import the Button component for the login button
    import Button from 'primevue/button';


    import { ref } from "vue";

    // Define the menu items for the Menubar
    const items = ref([
        {
            label: 'Webex',
            icon: 'pi pi-desktop',
        },
        {
            label: 'Home',
            icon: 'pi pi-home'
        },
        {
            label: 'Features',
            icon: 'pi pi-star'
        },
        {
            label: 'About Us',
            icon: 'pi pi-search',
        },
        {
            label: 'Contact',
            icon: 'pi pi-envelope'
        }
    ]);
</script>

<template>
    <div class="card">
        <Menubar :model="items">
            <template #end>
                <Button label="Login" icon="pi pi-user" class="mr-2" severity="info" />
                <Button label="Register" icon="pi pi-user-plus" severity="secondary" />
            </template>
        </Menubar>
    </div>
</template>



