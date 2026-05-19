<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submitted = ref(false);

function handleSubmit() {
    submitted.value = true;
    setTimeout(() => {
        form.value = { name: '', email: '', subject: '', message: '' };
        submitted.value = false;
    }, 3000);
}
</script>

<template>
    <section class="content-section contact-section">
        <div class="contact-container">
            <span class="contact-label">Contact Us</span>
            <h2 class="contact-title">Get in Touch</h2>
            <p class="contact-description">
                Have a question or want to learn more? We'd love to hear from you.
                Fill out the form below and our team will get back to you shortly.
            </p>
            <div class="contact-grid">
                <form class="contact-form" @submit.prevent="handleSubmit">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="name">Full Name</label>
                            <InputText v-model="form.name" id="name" placeholder="John Doe" class="form-input" />
                        </div>
                        <div class="form-field">
                            <label for="email">Email Address</label>
                            <InputText v-model="form.email" id="email" type="email" placeholder="john@example.com" class="form-input" />
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="subject">Subject</label>
                        <InputText v-model="form.subject" id="subject" placeholder="How can we help?" class="form-input" />
                    </div>
                    <div class="form-field">
                        <label for="message">Message</label>
                        <textarea v-model="form.message" id="message" rows="5" placeholder="Write your message..." class="form-textarea" />
                    </div>
                    <Button type="submit" label="Send Message" icon="pi pi-send" class="contact-submit" :disabled="submitted">
                        <span v-if="submitted">Sent!</span>
                        <span v-else>Send Message</span>
                    </Button>
                </form>
                <div class="contact-info">
                    <div class="info-card">
                        <i class="pi pi-map-marker info-icon"></i>
                        <div>
                            <h4 class="info-heading">Address</h4>
                            <p class="info-text">123 Innovation Drive, Tech City, TC 10001</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <i class="pi pi-envelope info-icon"></i>
                        <div>
                            <h4 class="info-heading">Email</h4>
                            <p class="info-text">hello@webex.com</p>
                        </div>
                    </div>
                    <div class="info-card">
                        <i class="pi pi-phone info-icon"></i>
                        <div>
                            <h4 class="info-heading">Phone</h4>
                            <p class="info-text">+1 (555) 123-4567</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.contact-section {
    padding: 80px;
}

.contact-container {
    max-width: 1100px;
    margin: 0 auto;
    text-align: center;
}

.contact-label {
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

.contact-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 0.75rem;
    color: var(--page-text);
}

.contact-description {
    font-size: 1.125rem;
    color: var(--muted-text);
    line-height: 1.7;
    margin: 0 auto 3rem;
    max-width: 600px;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 2rem;
    text-align: left;
}

/* ── Form ── */
.contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-field label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111;
    letter-spacing: 0.02em;
}

/* Match LoginPage input design */
.form-input,
.form-textarea {
    width: 100%;
}

:deep(.p-inputtext) {
    width: 100%;
    padding: 0.75rem 0.875rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 12px;
    background: var(--surface-bg);
    color: var(--page-text);
    font-size: 0.875rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

:deep(.p-inputtext:focus) {
    outline: none;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

/* Native textarea styling to match inputs */
.form-textarea {
    resize: vertical;
    padding: 0.75rem 0.875rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 12px;
    background: var(--surface-bg);
    color: var(--page-text);
    font-size: 0.875rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    line-height: 1.5;
}

.form-textarea:focus {
    outline: none;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

.contact-submit {
    align-self: flex-start;
    margin-top: 0.5rem;
    border-radius: 12px;
}

/* ── Info Cards ── */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    justify-content: center;
}

.info-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 12px;
    background: var(--surface-bg);
    border: 1.5px solid var(--surface-border);
    transition: box-shadow 0.2s ease;
}

.info-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.info-icon {
    font-size: 1.25rem;
    color: #111;
    flex-shrink: 0;
    margin-top: 2px;
    line-height: 1;
}

.info-heading {
    font-size: 0.9rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 0.25rem;
}

.info-text {
    font-size: 0.875rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.5;
}

@media (max-width: 960px) {
    .contact-section {
        padding: 48px 20px;
    }
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .contact-title {
        font-size: 1.75rem;
    }
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
