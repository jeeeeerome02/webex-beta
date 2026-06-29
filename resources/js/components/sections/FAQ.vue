<script setup>
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import { faqItems } from '../../data/siteData';
</script>

<template>
    <section id="faq" class="content-section faq-section">
        <div class="faq-container">
            <div class="faq-header" v-reveal>
                <span class="faq-label">FAQ</span>
                <h2 class="faq-title">Frequently asked questions</h2>
                <p class="faq-subtitle">
                    Everything you need to know about the platform. Can't find an answer?
                    <a href="#contact" class="faq-link">Get in touch.</a>
                </p>
            </div>

            <div class="faq-accordion-wrap" v-reveal="{ delay: 120 }">
                <Accordion :value="['0']" multiple>
                    <AccordionPanel
                        v-for="(item, index) in faqItems"
                        :key="index"
                        :value="String(index)"
                    >
                        <AccordionHeader>{{ item.question }}</AccordionHeader>
                        <AccordionContent>
                            <p class="faq-answer">{{ item.answer }}</p>
                        </AccordionContent>
                    </AccordionPanel>
                </Accordion>
            </div>
        </div>
    </section>
</template>

<style scoped>
.faq-section {
    width: 100%;
}

.faq-container {
    width: min(100%, 820px);
    margin: 0 auto;
}

.faq-header {
    text-align: center;
    margin-bottom: 3rem;
}

.faq-label {
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

.faq-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 0.75rem;
    color: var(--page-text);
}

.faq-subtitle {
    font-size: 1.125rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.7;
}

.faq-link {
    color: var(--button-primary-bg);
    font-weight: 600;
    text-decoration: none;
}

.faq-link:hover {
    text-decoration: underline;
}

.faq-accordion-wrap :deep(.p-accordionpanel) {
    border: 1.5px solid var(--surface-border);
    border-radius: 12px;
    background: var(--surface-bg);
    margin-bottom: 0.875rem;
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.faq-accordion-wrap :deep(.p-accordionpanel:hover) {
    border-color: color-mix(in srgb, var(--button-primary-bg) 40%, var(--surface-border));
}

.faq-accordion-wrap :deep(.p-accordionpanel-active) {
    border-color: color-mix(in srgb, var(--button-primary-bg) 55%, var(--surface-border));
    box-shadow: 0 10px 30px color-mix(in srgb, var(--page-text) 8%, transparent);
}

.faq-accordion-wrap :deep(.p-accordionheader) {
    padding: 1.15rem 1.25rem;
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--page-text);
    background: transparent;
    border: none;
    gap: 1rem;
}

.faq-accordion-wrap :deep(.p-accordionheader:hover) {
    background: color-mix(in srgb, var(--button-primary-bg) 4%, transparent);
}

.faq-accordion-wrap :deep(.p-accordioncontent-content) {
    padding: 0 1.25rem 1.25rem;
    background: transparent;
    border: none;
}

.faq-answer {
    font-size: 0.9688rem;
    color: var(--muted-text);
    line-height: 1.7;
    margin: 0;
}

@media (max-width: 600px) {
    .faq-header {
        margin-bottom: 2rem;
    }
    .faq-title {
        font-size: 1.6rem;
    }
    .faq-subtitle {
        font-size: 1rem;
    }
    .faq-accordion-wrap :deep(.p-accordionheader) {
        font-size: 1rem;
        padding: 1rem;
    }
}
</style>
