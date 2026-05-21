<script setup>
import Button from 'primevue/button';
import { useRouter } from 'vue-router';
import {
    heroTagline,
    heroDescription,
    heroCard2Title,
    heroCard2Description,
} from '../../data/siteData';

const router = useRouter();

const scrollToFeatures = () => {
    document.getElementById('features')?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    });
};
</script>

<template>
    <section id="home" class="hero">
        <div class="abstract-background" aria-hidden="true">
            <span class="wave wave-back"></span>
            <span class="wave wave-middle"></span>
            <span class="wave wave-ribbon"></span>
            <span class="wave wave-front"></span>
            <span class="wave wave-glow"></span>
            <span class="wave wave-lower"></span>
            <span class="wave wave-sheen"></span>
        </div>
        <div class="card-container">

            <!-- Card 1 -->
            <div class="card">
                <div class="hero-content-left">
                    <h1>Smarter Online</h1>
                    <h2>Examination Starts Here</h2>
                    <p>{{ heroDescription }}</p>
                    <div class="hero-actions">
                        <Button
                            label="Try It Now"
                            icon="pi pi-play"
                            size="small"
                            severity="contrast"
                            class="btn-get-started"
                            @click="router.push('/register')"
                        />
                        <Button
                            label="Features"
                            icon="pi pi-sliders-h"
                            size="small"
                            severity="secondary"
                            class="btn-features"
                            @click="scrollToFeatures"
                        />
                    </div>
                </div>
                <p class="footer-hero">
                    {{ heroTagline }}
                </p>
            </div>

            <!-- Card 2 -->
            <div class="card">
                <div class="hero-content-right">
                    <h1 class="logo">{{ heroCard2Title }}</h1>
                    <p>{{ heroCard2Description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.hero {
    position: relative;
    overflow: hidden;
    isolation: isolate;
    display: flex;
    align-items: center;
    min-height: clamp(600px, 86svh, 780px);
    width: 100%;
    padding: 136px clamp(1.25rem, 5vw, 5rem) 72px;
    background: var(--content-bg);
}

.abstract-background {
    position: absolute;
    inset: 0;
    z-index: -2;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--content-bg);
}

.wave {
    position: absolute;
    left: -12%;
    width: 124%;
    border-radius: 0 0 52% 54% / 0 0 100% 100%;
    transform-origin: top left;
}

.wave-back {
    top: -48%;
    height: 82%;
    background: var(--wave-deep);
    transform: rotate(7deg);
}

.wave-middle {
    top: -38%;
    height: 82%;
    background: var(--wave-strong);
    transform: rotate(12deg);
}

.wave-ribbon {
    top: -28%;
    left: -16%;
    width: 132%;
    height: 80%;
    background: var(--wave-mid);
    transform: rotate(16deg);
    border-radius: 0 0 58% 62% / 0 0 100% 100%;
}

.wave-front {
    top: -16%;
    left: -22%;
    width: 140%;
    height: 78%;
    background: var(--content-bg);
    transform: rotate(19deg);
    border-radius: 0 0 64% 68% / 0 0 100% 100%;
}

.wave-glow {
    top: 4%;
    left: 26%;
    width: 92%;
    height: 58%;
    background: var(--wave-glow);
    transform: rotate(21deg);
    border-radius: 0 0 72% 80% / 0 0 100% 100%;
}

.wave-lower {
    top: 18%;
    left: -18%;
    width: 138%;
    height: 54%;
    background: var(--wave-soft);
    transform: rotate(13deg);
    border-radius: 0 0 58% 54% / 0 0 100% 100%;
}

.wave-sheen {
    top: 27%;
    left: 42%;
    width: 72%;
    height: 42%;
    background: color-mix(in srgb, var(--content-bg) 74%, transparent);
    transform: rotate(20deg);
    border-radius: 0 0 78% 82% / 0 0 100% 100%;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.btn-get-started {
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    border-color: var(--button-primary-bg);
}

.btn-get-started:hover {
    color: var(--button-secondary-text);
    background: var(--button-secondary-bg);
    border-color: var(--button-border);
}

.btn-features {
    color: var(--button-secondary-text);
    background: var(--button-secondary-bg);
    border-color: var(--button-border);
}

.btn-features:hover {
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    border-color: var(--button-primary-bg);
}

.card-container {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.65fr);
    align-items: center;
    gap: clamp(2rem, 6vw, 5rem);
    width: min(100%, 1180px);
    margin: 0 auto;
}

.card {
    min-width: 0;
    padding: 0;
    border: none;
    border-radius: 8px;
    background: transparent;
    backdrop-filter: none;
}

.card:last-child {
    align-self: stretch;
    display: flex;
    align-items: center;
    border-left: 1px solid var(--surface-border);
    padding-left: clamp(1.5rem, 4vw, 3rem);
}

.hero-content-left h1 {
    font-weight: bold;
    font-size: clamp(3.25rem, 7vw, 5.75rem);
    line-height: 0.95;
    margin: 0;
    color: var(--page-text);
    letter-spacing: 0;
}

.hero-content-left h2 {
    font-weight: bold;
    font-size: clamp(2.25rem, 5vw, 4.5rem);
    line-height: 1;
    margin: 0.25rem 0 1.25rem;
    color: var(--page-text);
    letter-spacing: 0;
}

.hero-content-right .logo {
    font-size: clamp(1.75rem, 3vw, 2.25rem);
    line-height: 1.1;
    margin: 0 0 1rem;
    color: var(--page-text);
    letter-spacing: 0;
}

.hero-content-right p,
.hero-content-left p {
    max-width: 640px;
    font-size: clamp(1rem, 1.6vw, 1.25rem);
    color: var(--muted-text);
    line-height: 1.65;
    margin: 0;
}

.footer-hero {
    max-width: 520px;
    font-size: 16px;
    color: var(--muted-text);
    line-height: 1.6;
    margin: 2rem 0 0;
}

@media (max-width: 960px) {
    .hero {
        min-height: auto;
        padding: 108px 1.25rem 48px;
    }

    .card-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .card:last-child {
        border-left: 0;
        border-top: 1px solid var(--surface-border);
        padding: 1.5rem 0 0;
    }
}
@media (max-width: 600px) {
    .hero {
        padding: 88px 1rem 40px;
    }
    .card-container {
        gap: 1.5rem;
    }
    .hero-content-left h1 {
        font-size: 2.85rem;
    }
    .hero-content-left h2 {
        font-size: 1.95rem;
    }
    .hero-content-right .logo {
        font-size: 1.5rem;
    }
    .hero-content-right p,
    .hero-content-left p {
        font-size: 1rem;
    }
    .footer-hero {
        font-size: 0.95rem;
    }
    .btn-get-started,
    .btn-features {
        width: 100%;
        font-size: 0.95rem;
        padding: 0.6rem 0.8rem;
    }
}
</style>
