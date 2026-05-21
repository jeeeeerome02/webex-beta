<script setup>
import Navbar from '../components/layout/Navbar.vue';
import HeroMain from '../components/sections/HeroMain.vue';
import Features from '../components/sections/Features.vue';
import Testimonials from '../components/sections/Testimonials.vue';
import About from '../components/sections/About.vue';
import Contact from '../components/sections/Contact.vue';
import Footer from '../components/sections/Footer.vue';
</script>

<template>
    <Navbar />
    <main class="site-content">
        <HeroMain />
        <Features />
        <Testimonials />
        <About />
        <Contact />
        <Footer />
    </main>
</template>

<style scoped>
.site-content {
    --section-x: clamp(1.25rem, 5vw, 5rem);
    --section-y: clamp(3.5rem, 8vw, 5rem);
    min-height: 100vh;
    color: var(--page-text);
    background: var(--content-bg);
    transition: color 0.2s ease, background-color 0.2s ease;
}

.site-content :deep(.content-section:not(.footer-section)) {
    padding: var(--section-y) var(--section-x);
    background: var(--content-bg);
    border-top: none;
    scroll-margin-top: 72px;
}

.site-content :deep(.content-section h2) {
    color: var(--page-text);
}

@media (max-width: 960px) {
    .site-content :deep(.content-section:not(.footer-section)) {
        padding: 48px 20px;
    }
}
</style>
