<template>
    <section id="features" class="content-section features-section">
        <div class="features-container">
            <div class="features-header" v-reveal>
                <h2 class="features-title">Features</h2>
                <p class="features-subtitle">Discover what makes our product stand out</p>
            </div>
            <div class="features-grid">
                <div
                v-for="(feature, index) in features"
                :key="index"
                class="feature-card"
                v-reveal="{ delay: (index % 3) * 100 }"
            >
                <span class="feature-icon-wrap">
                    <span class="feature-icon" v-html="feature.icon"></span>
                </span>
                <h3 class="feature-title">{{ feature.title }}</h3>
                <p class="feature-text">{{ feature.description }}</p>
            </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.features-section {
    width: 100%;
}

.features-container {
    width: min(100%, 1180px);
    margin: 0 auto;
}

.features-header {
    max-width: 640px;
    margin-inline: auto;
    text-align: center;
    margin-bottom: 3rem;
}

.features-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: 0;
    color: var(--page-text);
}

.features-subtitle {
    font-size: 1.2rem;
    color: var(--muted-text);
    margin: 0.5rem 0 0 0;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: stretch;
    gap: clamp(1rem, 2vw, 1.5rem);
}

.feature-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.875rem;
    min-height: 230px;
    height: 100%;
    padding: 1.75rem;
    border-radius: 8px;
    background: var(--surface-bg);
    border: 1.5px solid var(--surface-border);
    box-shadow: 0 1px 2px color-mix(in srgb, var(--page-text) 6%, transparent);
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--button-primary-bg) 48%, var(--surface-border));
    box-shadow: 0 14px 34px color-mix(in srgb, var(--page-text) 10%, transparent);
}

.feature-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 8px;
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    flex: 0 0 auto;
}

.feature-icon {
    font-size: 1.2rem;
    color: currentColor;
    display: block;
    line-height: 1;
}

.feature-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--page-text);
    margin: 0;
    letter-spacing: 0;
}

.feature-text {
    font-size: 0.9375rem;
    color: var(--muted-text);
    line-height: 1.65;
    margin: 0;
    max-width: 34rem;
}

@media (max-width: 960px) {
    .features-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 600px) {
    .features-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .features-header {
        margin-bottom: 2rem;
        text-align: left;
    }
    .features-title {
        font-size: 1.75rem;
    }
    .features-subtitle {
        font-size: 1rem;
    }
    .feature-card {
        min-height: auto;
    }
}
</style>

<script setup>
import { features } from '../../data/siteData';
</script>
