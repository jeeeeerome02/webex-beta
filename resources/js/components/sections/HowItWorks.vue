<script setup>
import { howItWorks } from '../../data/siteData';
</script>

<template>
    <section id="how-it-works" class="content-section how-section">
        <div class="how-container">
            <div class="how-header" v-reveal>
                <span class="how-label">How It Works</span>
                <h2 class="how-title">From blank page to graded exam in three steps</h2>
                <p class="how-subtitle">
                    A streamlined workflow designed to remove busywork so you can focus on teaching.
                </p>
            </div>

            <div class="how-grid">
                <div
                    v-for="(item, index) in howItWorks"
                    :key="item.step"
                    class="how-card"
                    v-reveal="{ delay: index * 120 }"
                >
                    <span class="how-step">Step {{ item.step }}</span>
                    <span class="how-icon-wrap">
                        <span class="how-icon" v-html="item.icon"></span>
                    </span>
                    <h3 class="how-card-title">{{ item.title }}</h3>
                    <p class="how-card-text">{{ item.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.how-section {
    width: 100%;
}

.how-container {
    width: min(100%, 1180px);
    margin: 0 auto;
}

.how-header {
    max-width: 680px;
    margin-inline: auto;
    text-align: center;
    margin-bottom: 3.5rem;
}

.how-label {
    display: inline-block;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--button-primary-bg);
    background: color-mix(in srgb, var(--button-primary-bg) 8%, transparent);
    padding: 6px 16px;
    border-radius: 999px;
    margin-bottom: 1rem;
}

.how-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 0.75rem;
    line-height: 1.2;
    color: var(--page-text);
}

.how-subtitle {
    font-size: 1.125rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.7;
}

.how-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1rem, 2.5vw, 2rem);
    position: relative;
}

.how-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 2rem 1.75rem;
    border-radius: 12px;
    background: var(--surface-bg);
    border: 1.5px solid var(--surface-border);
    box-shadow: 0 1px 2px color-mix(in srgb, var(--page-text) 6%, transparent);
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.how-card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--button-primary-bg) 48%, var(--surface-border));
    box-shadow: 0 18px 40px color-mix(in srgb, var(--page-text) 12%, transparent);
}

.how-step {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--muted-text);
}

.how-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 12px;
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    flex: 0 0 auto;
}

.how-icon {
    font-size: 1.4rem;
    line-height: 1;
    display: block;
}

.how-card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--page-text);
    margin: 0;
}

.how-card-text {
    font-size: 0.9375rem;
    color: var(--muted-text);
    line-height: 1.65;
    margin: 0;
}

@media (max-width: 960px) {
    .how-grid {
        grid-template-columns: 1fr;
    }
    .how-title {
        font-size: 2rem;
    }
}

@media (max-width: 600px) {
    .how-header {
        margin-bottom: 2.5rem;
    }
    .how-title {
        font-size: 1.6rem;
    }
    .how-subtitle {
        font-size: 1rem;
    }
}
</style>
