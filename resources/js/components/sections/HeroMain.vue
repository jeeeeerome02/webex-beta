<script setup>
// Hero section
</script>

<template>
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Webex Beta</h1>
            <p>
                Experience the future of communication with our cutting-edge features
                and seamless collaboration tools.
            </p>
            <button class="cta-button">Get Started</button>
        </div>
    </section>
</template>

<style scoped>
.hero {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 1.5rem;
    text-align: center;
}

.hero-content {
    max-width: 720px;
}

.hero-content h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.hero-content p {
    font-size: 1.125rem;
    color: #555;
    margin-bottom: 1.5rem;
}

.cta-button {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
    background-color: #2563eb;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.cta-button:hover {
    background-color: #1d4ed8;
}
</style>
