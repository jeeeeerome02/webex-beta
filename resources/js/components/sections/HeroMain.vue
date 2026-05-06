<script setup>
// Hero section
import Button from 'primevue/button';
</script>

<template>
    <section class="hero">
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
                    <p>
                        Let AI help you create questions and review results automatically. Run exams smoothly without the usual hassle.
                    </p>
                     <Button
                        label="Try It Now"
                        icon="pi pi-user"
                        size="small"
                        severity="contrast"
                        class="btn-get-started"
                    />
                    <Button label="Features" icon="pi pi-user" size="small" severity="secondary" class="btn-features" />

                </div>
                <p class="footer-hero">
                        Zero hassle, just smarter exams. Try it now and see the difference!
                </p>
            </div>

            <!-- Card 2 -->
            <div class="card">
                <div class="hero-content-right">
                    <h1 class="logo">Lorem Ipsum</h1>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </p>
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
    min-height: 100vh;
    width: 100%;
    padding: 140px 80px 60px;
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

.btn-get-started,
.btn-features {
    margin-top: 12px;
    margin-right: 8px;
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
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.card {
    padding: 30px;
    border: none;
    border-radius: 8px;
    width: 45%;
    background: transparent;
    backdrop-filter: none;
}

.card:first-child {
    width: 55%;
}

.card:last-child {
    width: 35%;
}

.hero-content-left h1 {
    font-weight: bold;
    font-size: 78px;
    color: var(--page-text);
}

.hero-content-left h2 {
    font-weight: bold;
    font-size: 62px;
    color: var(--page-text);
}

.hero-content-right .logo {
    font-size: 32px;
    color: var(--page-text);
}

.hero-content-right p,
.hero-content-left p {
    font-size: 20px;
    color: var(--muted-text);
    margin-bottom: 10px;
}

.footer-hero {
    font-size: 16px;
    color: var(--muted-text);
    margin-top: 20px;
}

@media (max-width: 960px) {
    .hero {
        padding: 110px 20px 30px;
    }

    .card,
    .card:first-child,
    .card:last-child {
        width: 100%;
    }

    .btn-get-started,
    .btn-features {
        width: 100%;
    }
}
</style>


