<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { aboutTitle, aboutDescription, aboutStats } from '../../data/siteData';

const aboutImage =
    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80';

const highlights = [
    {
        title: 'Curriculum-aware questions',
        text: 'AI drafts questions from your own material, so every assessment stays aligned with what you actually teach.',
    },
    {
        title: 'Consistent grading',
        text: 'Answers are evaluated the same way every time, removing the fatigue and bias of manual marking.',
    },
    {
        title: 'Insight that matters',
        text: 'Clear analytics show where each student stands and where the class needs another look.',
    },
];

// Apple-style parallax: image drifts slowly while the section scrolls through view.
const heroEl = ref(null);
const imageEl = ref(null);
let frame = 0;
const reduceMotion =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const updateParallax = () => {
    frame = 0;
    if (!heroEl.value || !imageEl.value) return;

    const rect = heroEl.value.getBoundingClientRect();
    const viewportH = window.innerHeight || 1;

    if (rect.bottom < 0 || rect.top > viewportH) return;

    // progress: 0 when section enters bottom, 1 when it leaves top.
    const progress = (viewportH - rect.top) / (viewportH + rect.height);
    const shift = (progress - 0.5) * 80; // px of drift
    imageEl.value.style.transform = `translate3d(0, ${shift.toFixed(2)}px, 0) scale(1.18)`;
};

const onScroll = () => {
    if (frame) return;
    frame = window.requestAnimationFrame(updateParallax);
};

onMounted(() => {
    if (reduceMotion) return;
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    updateParallax();
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
    if (frame) window.cancelAnimationFrame(frame);
});
</script>

<template>
    <section id="about" class="content-section about-section">
        <div ref="heroEl" class="about-figure">
            <div class="about-image-wrap">
                <img
                    ref="imageEl"
                    :src="aboutImage"
                    alt="Educators collaborating on the platform"
                    class="about-bg-image"
                    loading="lazy"
                />
            </div>
            <div class="about-figure-overlay"></div>

            <div class="about-figure-content">
                <span class="about-eyebrow" v-reveal>About Us</span>
                <h2 class="about-title" v-reveal="{ delay: 80 }">{{ aboutTitle }}</h2>
                <p class="about-description" v-reveal="{ delay: 180 }">{{ aboutDescription }}</p>
            </div>
        </div>

        <div class="about-points">
            <div
                v-for="(point, index) in highlights"
                :key="point.title"
                class="about-point"
                v-reveal="{ delay: index * 120 }"
            >
                <span class="about-point-index">{{ String(index + 1).padStart(2, '0') }}</span>
                <h3 class="about-point-title">{{ point.title }}</h3>
                <p class="about-point-text">{{ point.text }}</p>
            </div>
        </div>

        <div class="about-stats">
            <div
                v-for="(stat, index) in aboutStats"
                :key="stat.label"
                class="about-stat"
                v-reveal="{ delay: index * 90 }"
            >
                <span class="about-stat-number">{{ stat.value }}</span>
                <span class="about-stat-label">{{ stat.label }}</span>
            </div>
        </div>
    </section>
</template>

<style scoped>
.about-section {
    width: 100%;
}

/* ── Text-in-image (left-aligned overlay) ── */
.about-figure {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    width: min(100%, 1180px);
    margin: 0 auto 3.5rem;
    min-height: clamp(440px, 64vh, 620px);
    border-radius: 24px;
}

.about-image-wrap {
    position: absolute;
    inset: 0;
    z-index: -2;
    overflow: hidden;
}

.about-bg-image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1.18);
    will-change: transform;
}

.about-figure-overlay {
    position: absolute;
    inset: 0;
    z-index: -1;
    background: linear-gradient(
        90deg,
        rgba(0, 0, 0, 0.78) 0%,
        rgba(0, 0, 0, 0.55) 38%,
        rgba(0, 0, 0, 0.12) 72%,
        rgba(0, 0, 0, 0) 100%
    ),
    linear-gradient(180deg, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0.45) 100%);
}

.about-figure-content {
    position: relative;
    max-width: 640px;
    padding: clamp(2rem, 5vw, 4rem);
    text-align: left;
    color: #fff;
}

.about-eyebrow {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.72);
    margin-bottom: 1.25rem;
}

.about-title {
    font-size: clamp(2.25rem, 5vw, 3.75rem);
    font-weight: 700;
    margin: 0 0 1.25rem;
    line-height: 1.05;
    letter-spacing: -0.03em;
    color: #fff;
    text-wrap: balance;
}

.about-description {
    font-size: clamp(1.0625rem, 1.5vw, 1.25rem);
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.65;
    letter-spacing: -0.005em;
    margin: 0;
    max-width: 540px;
}

/* ── Points (editorial, no icons/bubbles) ── */
.about-points {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: clamp(1.5rem, 4vw, 3rem);
    width: min(100%, 1180px);
    margin: 0 auto 3.5rem;
}

.about-point {
    padding-top: 1.5rem;
    border-top: 1px solid var(--surface-border);
}

.about-point-index {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--muted-text);
    margin-bottom: 0.75rem;
    font-variant-numeric: tabular-nums;
}

.about-point-title {
    font-size: 1.1875rem;
    font-weight: 650;
    letter-spacing: -0.015em;
    color: var(--page-text);
    margin: 0 0 0.5rem;
}

.about-point-text {
    font-size: 0.9688rem;
    color: var(--muted-text);
    line-height: 1.65;
    margin: 0;
}

/* ── Stats ── */
.about-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    width: min(100%, 1180px);
    margin: 0 auto;
    padding-top: 3rem;
    border-top: 1px solid var(--surface-border);
}

.about-stat {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    text-align: left;
}

.about-stat-number {
    font-size: clamp(2.25rem, 4vw, 3rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--page-text);
    line-height: 1;
}

.about-stat-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    font-weight: 500;
}

@media (max-width: 960px) {
    .about-figure {
        min-height: clamp(420px, 70vh, 560px);
        align-items: flex-end;
    }
    .about-figure-overlay {
        background: linear-gradient(
            180deg,
            rgba(0, 0, 0, 0.15) 0%,
            rgba(0, 0, 0, 0.55) 60%,
            rgba(0, 0, 0, 0.82) 100%
        );
    }
    .about-points {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .about-point {
        padding: 1.5rem 0;
    }
    .about-point + .about-point {
        border-top: 1px solid var(--surface-border);
    }
    .about-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.75rem;
    }
}

@media (max-width: 600px) {
    .about-figure {
        min-height: 480px;
    }
    .about-stats {
        gap: 1.5rem;
    }
}
</style>
