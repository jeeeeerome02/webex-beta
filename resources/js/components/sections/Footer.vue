<script setup>
import AppLogo from '../layout/AppLogo.vue';
import {
    siteName,
    footerTagline as tagline,
    productLinks,
    companyLinks,
    supportLinks,
} from '../../data/siteData';
</script>

<template>
    <footer class="content-section footer-section">
        <div class="footer-container">
            <div class="footer-top">
                <div class="footer-brand">
                    <AppLogo :size="30" :textSize="16" />
                    <p class="footer-tagline">{{ tagline }}</p>
                </div>
                <div class="footer-links-grid">
                    <div class="footer-col">
                        <h4 class="footer-col-title">Product</h4>
                        <a
                            v-for="link in productLinks"
                            :key="link.label"
                            :href="link.href"
                            class="footer-link"
                        >
                            {{ link.label }}
                        </a>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-col-title">Company</h4>
                        <a
                            v-for="link in companyLinks"
                            :key="link.label"
                            :href="link.href"
                            class="footer-link"
                        >
                            {{ link.label }}
                        </a>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-col-title">Support</h4>
                        <a
                            v-for="link in supportLinks"
                            :key="link.label"
                            :href="link.href"
                            class="footer-link"
                        >
                            {{ link.label }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                <p class="footer-copy">
                    &copy; {{ new Date().getFullYear() }} {{ siteName }}. All rights reserved.
                </p>
                <div class="footer-legal">
                    <a href="#" class="footer-legal-link">Privacy Policy</a>
                    <a href="#" class="footer-legal-link">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.footer-section {
    padding: 60px clamp(1.25rem, 5vw, 5rem) 40px;
    border-top: 1px solid var(--surface-border);
}

.footer-container {
    width: min(100%, 1180px);
    margin: 0 auto;
}

.footer-top {
    display: flex;
    justify-content: space-between;
    gap: 3rem;
    margin-bottom: 2.5rem;
}

.footer-brand {
    max-width: 260px;
}

.footer-tagline {
    font-size: 0.9375rem;
    color: var(--muted-text);
    line-height: 1.6;
    margin: 0.75rem 0 0;
}

.footer-links-grid {
    display: flex;
    gap: 4rem;
}

.footer-col {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.footer-col-title {
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--page-text);
    margin: 0 0 0.25rem;
}

.footer-link {
    font-size: 0.875rem;
    color: var(--muted-text);
    text-decoration: none;
    transition: color 0.15s ease;
}

.footer-link:hover {
    color: var(--page-text);
}

.footer-divider {
    height: 1px;
    background: var(--surface-border);
    margin-bottom: 1.5rem;
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-copy {
    font-size: 0.8125rem;
    color: var(--muted-text);
    margin: 0;
}

.footer-legal {
    display: flex;
    gap: 1.5rem;
}

.footer-legal-link {
    font-size: 0.8125rem;
    color: var(--muted-text);
    text-decoration: none;
    transition: color 0.15s ease;
}

.footer-legal-link:hover {
    color: var(--page-text);
}

@media (max-width: 960px) {
    .footer-section {
        padding: 48px 20px 32px;
    }
    .footer-top {
        flex-direction: column;
        gap: 2rem;
    }
    .footer-links-grid {
        gap: 2rem;
        flex-wrap: wrap;
    }
}

@media (max-width: 600px) {
    .footer-bottom {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
}
</style>
