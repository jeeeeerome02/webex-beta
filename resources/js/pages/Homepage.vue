<script setup>
import Navbar from '../components/layout/Navbar.vue';
import HeroMain from '../components/sections/HeroMain.vue';
import Features from '../components/sections/Features.vue';
import About from '../components/sections/About.vue';
import Contact from '../components/sections/Contact.vue';
import Footer from '../components/sections/Footer.vue';
</script>

<template>
    <Navbar />
    <main class="site-content">
        <HeroMain />
        <Features />
        <About />
        <Contact />
        <Footer />
    </main>
</template>

<style scoped>
.site-content {
    min-height: 100vh;
    color: var(--page-text);
    background: var(--content-bg);
    transition: color 0.2s ease, background-color 0.2s ease;
}

.site-content :deep(.content-section) {
    padding: 80px;
    background: var(--content-bg);
    border-top: none;
}

.site-content :deep(.content-section h2) {
    color: var(--page-text);
}

@media (max-width: 960px) {
    .site-content :deep(.content-section) {
        padding: 48px 20px;
    }
}
</style>
