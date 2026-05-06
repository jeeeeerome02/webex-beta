<script setup>
import logo from '../../assets/images/logo.png';
</script>

<template>
    <span class="app-logo-wrap" aria-label="Webex">
        <span class="logo-mark" aria-hidden="true">
            <img :src="logo" alt="" class="app-logo" />
        </span>
        <span class="logo-text">webex</span>
    </span>
</template>

<style scoped>
.app-logo-wrap {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.logo-mark {
    display: inline-flex;
    width: 28px;
    height: 28px;
    overflow: hidden;
}

.app-logo {
    height: 28px;
    width: auto;
    max-width: none;
    transform: translateX(-12px) scale(1.45);
    transform-origin: left center;
}

.logo-text {
    color: var(--navbar-text);
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1;
    letter-spacing: 0;
}

@media (max-width: 960px) {
    .logo-mark {
        width: 24px;
        height: 24px;
    }

    .app-logo {
        height: 24px;
        transform: translateX(-10px) scale(1.45);
    }

    .logo-text {
        font-size: 1.1rem;
    }
}
</style>
