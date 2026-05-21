<script setup>
// No direct imports, use <router-view />
</script>

<template>
    <div class="app-container">
        <!-- Default view (homepage) - shown when NOT on auth pages -->
        <router-view v-if="!isAuthPage" />
        
        <!-- Auth layout: left panel (static) + right panel (animated, scrollable) -->
        <div v-else class="auth-wrapper">
            <div class="left-panel-wrapper">
                <router-view name="left" />
            </div>
            <div class="right-panel">
                <!-- Scrollable wrapper with max-height constraint -->
                <div class="right-scroll-wrapper">
                    <router-view 
                        name="right" 
                        v-slot="{ Component, route }"
                    >
                        <transition 
                            :name="route.meta.transition || 'slide-horizontal'"
                            mode="out-in"
                        >
                            <component :is="Component" :key="route.name" />
                        </transition>
                    </router-view>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    computed: {
        isAuthPage() {
            return ['Login', 'Register'].includes(this.$route.name);
        }
    },
    methods: {
        onBeforeEnter(el) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}
</script>

<style scoped>
.app-container {
    min-height: 100vh;
    width: 100%;
}

/* Auth wrapper - splits screen */
.auth-wrapper {
    display: flex;
    height: 100vh;
    width: 100%;
    position: relative;
    overflow: hidden;
}

/* Left panel wrapper - fixed, never scrolls */
.left-panel-wrapper {
    flex: 1;
    height: 100%;
    overflow: hidden;
}

/* Hide left panel on smaller screens */
@media (max-width: 1024px) {
    .left-panel-wrapper {
        display: none;
    }
}

/* Right panel - centered container, no direct scroll */
.right-panel {
    --page-text: #111111;
    --muted-text: #555555;
    --surface-bg: #ffffff;
    --surface-border: rgba(17, 17, 17, 0.14);
    --button-primary-bg: #111111;
    --button-primary-text: #ffffff;
    --button-secondary-bg: transparent;
    --button-secondary-text: #111111;
    --button-border: #111111;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #ffffff;
    min-width: 0;
    overflow: hidden;
}

/* Inner scrollable wrapper: contains the form, centers horizontally, scrolls vertically when needed */
.right-scroll-wrapper {
    width: 100%;
    max-width: min(560px, 100%);
    max-height: calc(100vh - 3rem);
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    box-sizing: border-box;
    padding: 0.25rem;
    overscroll-behavior: contain;
    /* Hide scrollbar but keep functionality */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
}
.right-scroll-wrapper::-webkit-scrollbar {
    width: 0;
    height: 0;
}

@media (max-width: 768px) {
    .auth-wrapper {
        height: auto;
        min-height: 100vh;
        overflow: visible;
    }

    .right-panel {
        align-items: flex-start;
        min-height: 100vh;
        padding: 1.25rem;
        overflow: visible;
    }

    .right-scroll-wrapper {
        max-width: 100%;
        max-height: none;
        overflow: visible;
        padding: 0;
    }
}
</style>

<!-- Global transition styles (unscoped) -->
<style>
.slide-horizontal-enter-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-horizontal-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-horizontal-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.slide-horizontal-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>
