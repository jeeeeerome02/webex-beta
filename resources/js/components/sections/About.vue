<template>
    <section class="content-section about-section">
        <div class="about-container">
            <span class="about-label">About Us</span>
            <h2 class="about-title">Empowering Educators with<br>Smart Exam Technology</h2>
            <p class="about-description">
                Our platform leverages cutting-edge artificial intelligence to transform the way exams are created,
                delivered, and graded. We believe that educators deserve better tools — tools that save time,
                reduce errors, and unlock deeper insights into student learning.
            </p>
            <div class="about-grid">
                <div class="about-stat">
                    <span class="about-stat-number">50K+</span>
                    <span class="about-stat-label">Exams Created</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-number">12K+</span>
                    <span class="about-stat-label">Active Educators</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-number">98%</span>
                    <span class="about-stat-label">Grading Accuracy</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-number">200+</span>
                    <span class="about-stat-label">Institutions</span>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.about-section {
    padding: 80px;
}

.about-container {
    max-width: 900px;
    margin: 0 auto;
    text-align: center;
}

.about-label {
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

.about-title {
    font-size: 2.75rem;
    font-weight: 800;
    margin: 0 0 1.5rem;
    line-height: 1.2;
    color: var(--page-text);
}

.about-description {
    font-size: 1.125rem;
    color: var(--muted-text);
    line-height: 1.75;
    margin: 0 auto 3rem;
    max-width: 680px;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

.about-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.about-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--page-text);
    line-height: 1;
}

.about-stat-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    font-weight: 500;
}

@media (max-width: 960px) {
    .about-section {
        padding: 48px 20px;
    }
    .about-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .about-title {
        font-size: 1.75rem;
    }
    .about-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
}
</style>
