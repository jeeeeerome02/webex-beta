<template>
    <section class="features-section">
        <div class="features-header">
            <h2 class="features-title">Features</h2>
            <p class="features-subtitle">Discover what makes our product stand out</p>
        </div>
        <div class="features-grid">
            <div
                v-for="(feature, index) in features"
                :key="index"
                class="feature-card"
            >
                <span class="feature-icon" v-html="feature.icon"></span>
                <h3 class="feature-title">{{ feature.title }}</h3>
                <p class="feature-text">{{ feature.description }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.features-section {
    width: 100%;
    padding: 80px 0;
}

.features-header {
    text-align: center;
    margin-bottom: 3rem;
}

.features-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: -1px;
    color: #111;
}

.features-subtitle {
    font-size: 1.2rem;
    color: var(--muted-text);
    margin: 0.5rem 0 0 0;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    padding: 0 150px;
}

.feature-card {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.75rem;
    border-radius: 12px;
    background: #fff;
    border: 1.5px solid var(--surface-border);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}

.feature-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border-color: #111;
}

.feature-icon {
    font-size: 1.5rem;
    color: #111;
    display: block;
    line-height: 1;
}

.feature-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111;
    margin: 0;
    letter-spacing: -0.01em;
}

.feature-text {
    font-size: 0.9375rem;
    color: var(--muted-text);
    line-height: 1.65;
    margin: 0;
}

@media (max-width: 1200px) {
    .features-grid {
        padding: 0 2rem;
    }
}

@media (max-width: 960px) {
    .features-section {
        padding: 48px 20px;
    }
    .features-grid {
        grid-template-columns: 1fr 1fr;
        padding: 0 1rem;
    }
}

@media (max-width: 600px) {
    .features-grid {
        grid-template-columns: 1fr;
        padding: 0 0.5rem;
    }
    .features-title {
        font-size: 1.75rem;
    }
}
</style>

<script setup>
const features = [
    {
        icon: '<i class="pi pi-bolt"></i>',
        title: 'AI-Powered Questions',
        description: 'Automatically generate high-quality exam questions tailored to your curriculum with a single click.',
    },
    {
        icon: '<i class="pi pi-star"></i>',
        title: 'Smart Grading',
        description: 'Let AI evaluate answers instantly and consistently, freeing up your time for what matters most.',
    },
    {
        icon: '<i class="pi pi-cog"></i>',
        title: 'Custom Templates',
        description: 'Create and save exam templates that match your institution\'s format and difficulty standards.',
    },
    {
        icon: '<i class="pi pi-globe"></i>',
        title: 'Cloud-Based',
        description: 'Access your exams and results anywhere, anytime — securely stored in the cloud.',
    },
    {
        icon: '<i class="pi pi-users"></i>',
        title: 'Student Analytics',
        description: 'Detailed per-student insights and at-a-glance class performance dashboards.',
    },
    {
        icon: '<i class="pi pi-lock"></i>',
        title: 'Secure & Private',
        description: 'End-to-end encryption and role-based access keep your data safe and compliant.',
    },
];
</script>
