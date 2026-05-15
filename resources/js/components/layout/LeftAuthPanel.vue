<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLogo from './AppLogo.vue';
import loginBg from '../../assets/images/login-background.jpg';

const route = useRoute();

const title = computed(() =>
    route.meta.leftTitle || 'Smarter Online Examination Starts Here'
);
const description = computed(() =>
    route.meta.leftDescription || 'Let AI help you create questions and review results automatically. Run exams smoothly without the usual hassle.'
);
</script>

<template>
    <div class="left-panel">
        <div class="image-container" :style="{ backgroundImage: `url(${loginBg})` }">
            <div class="gradient-overlay"></div>
            <div class="panel-content">
                <div class="logo-wrapper">
                    <AppLogo :size="70" :textSize="32" textColor="#ffffff" />
                </div>
                
                <!-- Animated text content -->
                <div class="text-content">
                    <transition name="fade" mode="out-in">
                        <div :key="route.fullPath" class="text-wrapper">
                            <h1 class="panel-title" v-html="title"></h1>
                            <p class="panel-description">{{ description }}</p>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.left-panel {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: flex-end;
}

.image-container {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgba(0, 0, 0, 0.85) 0%,
        rgba(0, 0, 0, 0.6) 40%,
        rgba(0, 0, 0, 0.75) 100%
    );
}

.panel-content {
    position: relative;
    z-index: 2;
    padding: 4rem;
    max-width: 480px;
    color: #fff;
}

.logo-wrapper {
    margin-bottom: 2rem;
}

.panel-title {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1.1;
    margin: 0 0 1.5rem 0;
    letter-spacing: -1px;
}

.panel-description {
    font-size: 1.125rem;
    line-height: 1.6;
    opacity: 0.9;
    margin: 0;
}

/* Text content animation */
.text-content {
    display: block;
}

.text-wrapper {
    display: block;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@media (max-width: 1024px) {
    .left-panel {
        display: none;
    }
}

@media (max-width: 768px) {
    .panel-title {
        font-size: 2rem;
    }

    .panel-description {
        font-size: 1rem;
    }

    .panel-content {
        padding: 2rem;
    }
}

@media (max-width: 480px) {
    .panel-title {
        font-size: 1.75rem;
    }

    .panel-content {
        padding: 1.5rem;
    }
}
</style>
