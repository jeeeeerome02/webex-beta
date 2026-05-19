<template>
    <footer class="content-section footer-section">
        <div class="footer-container">
            <div class="footer-top">
                <div class="footer-brand">
                    <div class="footer-logo">Web<span class="footer-logo-accent">ex</span></div>
                    <p class="footer-tagline">Empowering educators with smart exam technology.</p>
                </div>
                <div class="footer-links-grid">
                    <div class="footer-col">
                        <h4 class="footer-col-title">Product</h4>
                        <a href="#" class="footer-link">Features</a>
                        <a href="#" class="footer-link">Pricing</a>
                        <a href="#" class="footer-link">Integrations</a>
                        <a href="#" class="footer-link">Changelog</a>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-col-title">Company</h4>
                        <a href="#" class="footer-link">About</a>
                        <a href="#" class="footer-link">Blog</a>
                        <a href="#" class="footer-link">Careers</a>
                        <a href="#" class="footer-link">Press</a>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-col-title">Support</h4>
                        <a href="#" class="footer-link">Help Center</a>
                        <a href="#" class="footer-link">Documentation</a>
                        <a href="#" class="footer-link">Community</a>
                        <a href="#" class="footer-link">Status</a>
                    </div>
                </div>
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                <p class="footer-copy">&copy; {{ new Date().getFullYear() }} Webex. All rights reserved.</p>
                <div class="footer-legal">
                    <a href="#" class="footer-legal-link">Privacy Policy</a>
                    <a href="#" class="footer-legal-link">Terms of Service</a>
                    <a href="#" class="footer-legal-link">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.footer-section {
    padding: 60px 150px 40px;
    border-top: 1px solid var(--surface-border);
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

.footer-top {
    display: flex;
    justify-content: space-between;
    gap: 3rem;
    margin-bottom: 2.5rem;
}

.footer-brand {
    max-width: 280px;
}

.footer-logo {
    font-size: 1.625rem;
    font-weight: 800;
    color: var(--page-text);
    letter-spacing: -0.5px;
}

.footer-logo-accent {
    color: var(--button-primary-bg);
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
