<script setup>
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import { pricingPlans } from '../../data/siteData';

const router = useRouter();

const handleCta = (plan) => {
    if (plan.name === 'Enterprise') {
        document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }
    router.push('/register');
};
</script>

<template>
    <section id="pricing" class="content-section pricing-section">
        <div class="pricing-container">
            <div class="pricing-header" v-reveal>
                <span class="pricing-label">Pricing</span>
                <h2 class="pricing-title">Simple, transparent pricing</h2>
                <p class="pricing-subtitle">
                    Start free and upgrade as you grow. No hidden fees, cancel anytime.
                </p>
            </div>

            <div class="pricing-grid">
                <div
                    v-for="(plan, index) in pricingPlans"
                    :key="plan.name"
                    class="pricing-card"
                    :class="{ 'pricing-card--highlight': plan.highlight }"
                    v-reveal="{ delay: index * 120 }"
                >
                    <span v-if="plan.highlight" class="pricing-badge">Most Popular</span>
                    <h3 class="pricing-name">{{ plan.name }}</h3>
                    <p class="pricing-desc">{{ plan.description }}</p>
                    <div class="pricing-price">
                        <span class="pricing-amount">{{ plan.price }}</span>
                        <span class="pricing-period">{{ plan.period }}</span>
                    </div>
                    <ul class="pricing-features">
                        <li v-for="feature in plan.features" :key="feature">
                            <i class="pi pi-check"></i>
                            <span>{{ feature }}</span>
                        </li>
                    </ul>
                    <Button
                        :label="plan.cta"
                        class="pricing-cta"
                        :severity="plan.highlight ? 'contrast' : 'secondary'"
                        :outlined="!plan.highlight"
                        @click="handleCta(plan)"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pricing-section {
    width: 100%;
}

.pricing-container {
    width: min(100%, 1180px);
    margin: 0 auto;
}

.pricing-header {
    max-width: 640px;
    margin-inline: auto;
    text-align: center;
    margin-bottom: 3.5rem;
}

.pricing-label {
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

.pricing-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 0.75rem;
    color: var(--page-text);
}

.pricing-subtitle {
    font-size: 1.125rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.7;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: stretch;
    gap: clamp(1rem, 2.5vw, 1.75rem);
}

.pricing-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 2.25rem 1.75rem;
    border-radius: 16px;
    background: var(--surface-bg);
    border: 1.5px solid var(--surface-border);
    box-shadow: 0 1px 2px color-mix(in srgb, var(--page-text) 6%, transparent);
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.pricing-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 44px color-mix(in srgb, var(--page-text) 12%, transparent);
}

.pricing-card--highlight {
    border-color: var(--button-primary-bg);
    border-width: 2px;
    box-shadow: 0 18px 44px color-mix(in srgb, var(--button-primary-bg) 18%, transparent);
}

.pricing-badge {
    position: absolute;
    top: -0.85rem;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--button-primary-text);
    background: var(--button-primary-bg);
    padding: 5px 14px;
    border-radius: 999px;
    white-space: nowrap;
}

.pricing-name {
    font-size: 1.375rem;
    font-weight: 800;
    color: var(--page-text);
    margin: 0;
}

.pricing-desc {
    font-size: 0.9375rem;
    color: var(--muted-text);
    line-height: 1.55;
    margin: 0;
    min-height: 2.8em;
}

.pricing-price {
    display: flex;
    align-items: baseline;
    gap: 0.375rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--surface-border);
}

.pricing-amount {
    font-size: 2.75rem;
    font-weight: 800;
    color: var(--page-text);
    line-height: 1;
}

.pricing-period {
    font-size: 0.9375rem;
    color: var(--muted-text);
}

.pricing-features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    flex: 1 1 auto;
}

.pricing-features li {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    font-size: 0.9375rem;
    color: var(--page-text);
    line-height: 1.45;
}

.pricing-features i {
    color: var(--button-primary-bg);
    font-size: 0.875rem;
    margin-top: 0.2rem;
    flex: 0 0 auto;
}

.pricing-cta {
    width: 100%;
    margin-top: 0.5rem;
    font-weight: 600;
}

@media (max-width: 960px) {
    .pricing-grid {
        grid-template-columns: 1fr;
        max-width: 440px;
        margin-inline: auto;
    }
    .pricing-title {
        font-size: 2rem;
    }
    .pricing-desc {
        min-height: 0;
    }
}

@media (max-width: 600px) {
    .pricing-title {
        font-size: 1.6rem;
    }
    .pricing-subtitle {
        font-size: 1rem;
    }
}
</style>
