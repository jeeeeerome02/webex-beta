<script setup>
import { ref } from 'vue';
import { Form } from '@primevue/forms';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Checkbox from 'primevue/checkbox';
import { useRouter } from 'vue-router';

const router = useRouter();

const initialValues = ref({
    username: '',
    password: '',
    remember: false
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.username || !values.username.trim()) {
        errors.username = [{ message: 'Username is required.' }];
    }

    if (!values.password) {
        errors.password = [{ message: 'Password is required.' }];
    }

    return { errors };
};

const onFormSubmit = (e) => {
    if (e.valid) {
        console.log('Form is valid', e.states);
    }
};
</script>

<template>
    <div class="login-form-wrapper">
        <!-- Back to Homepage -->
        <button class="back-button" @click="router.push('/')">
            <i class="pi pi-arrow-left"></i>
            Back to Homepage
        </button>

        <div class="form-header">
            <h2 class="form-title">Sign in to your account</h2>
            <p class="form-subtitle">Welcome back! Please enter your credentials.</p>
        </div>

        <Form
            v-slot="$form"
            :resolver="resolver"
            :initialValues="initialValues"
            @submit="onFormSubmit"
            class="login-form"
        >
            <div class="form-field">
                <label for="username" class="form-label">Username</label>
                <InputText
                    id="username"
                    name="username"
                    type="text"
                    placeholder="Enter your username"
                    class="form-input"
                />
                <Message
                    v-if="$form.username?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.username.error?.message }}
                </Message>
            </div>

            <div class="form-field">
                <label for="password" class="form-label">Password</label>
                <Password
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter your password"
                    :feedback="false"
                    class="form-input"
                    inputClass="w-full"
                />
                <Message
                    v-if="$form.password?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.password.error?.message }}
                </Message>
            </div>

            <div class="form-options">
                <div class="checkbox-field">
                    <Checkbox name="remember" binary inputId="remember" />
                    <label for="remember" class="checkbox-label">Remember me</label>
                </div>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <Button
                type="submit"
                severity="secondary"
                label="Sign In"
                class="submit-btn"
            />
        </Form>

        <!-- Divider -->
        <div class="divider">
            <span>or continue with</span>
        </div>

        <!-- Social login buttons -->
        <div class="social-buttons">
            <Button
                severity="secondary"
                variant="outlined"
                class="social-btn"
                title="Sign in with Google"
            >
                <i class="pi pi-google" style="color: #DB4437; font-size: 1.2rem;"></i>
                <span>Google</span>
            </Button>

            <Button
                severity="secondary"
                variant="outlined"
                class="social-btn"
                title="Sign in with GitHub"
            >
                <i class="pi pi-github" style="color: #333; font-size: 1.2rem;"></i>
                <span>GitHub</span>
            </Button>
        </div>

        <!-- Sign up prompt -->
        <div class="signup-wrapper">
            <p class="signup-prompt">
                Don't have an account?
                <a 
                    href="/register" 
                    class="signup-link"
                    @click.prevent="router.push('/register')"
                >
                    Create one
                </a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.login-form-wrapper {
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin: 0 auto;
}

/* Back Button */
.back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
    background: none;
    border: none;
    color: var(--muted-text);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: color 0.2s;
}

.back-button:hover {
    color: #111;
}

.back-button i {
    font-size: 0.75rem;
}

/* Form Header */
.form-header {
    text-align: center;
    margin-bottom: 0.5rem;
}

.form-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 0.5rem 0;
}

.form-subtitle {
    font-size: 1rem;
    color: var(--muted-text);
    margin: 0;
}

/* Form Layout */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111;
    letter-spacing: 0.02em;
}

.form-input {
    width: 100%;
}

:deep(.p-inputtext),
:deep(.p-password input) {
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

:deep(.p-inputtext:focus),
:deep(.p-password-input:focus) {
    outline: none;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

:deep(.p-password .p-password-input) {
    padding-right: 2.5rem;
}

:deep(.p-password-overlay) {
    border-radius: 12px;
}

:deep(.p-message) {
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    margin-top: 0.25rem;
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    margin-top: 0.25rem;
}

.checkbox-field {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    margin: 0;
    user-select: none;
}

.forgot-link {
    font-size: 0.875rem;
    color: #111;
    text-decoration: none;
    font-weight: 600;
    transition: opacity 0.2s;
}

.forgot-link:hover {
    opacity: 0.7;
    text-decoration: underline;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 12px;
    margin-top: 0.5rem;
    background: #111;
    color: #fff;
    border: 1.5px solid #111;
    transition: all 0.2s ease;
}

.submit-btn:hover {
    background: #222;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

/* Divider */
.divider {
    display: flex;
    align-items: center;
    width: 100%;
    margin: 1.5rem 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--surface-border);
}

.divider span {
    padding: 0 1rem;
    font-size: 0.75rem;
    color: var(--muted-text);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
    white-space: nowrap;
}

/* Social Buttons */
.social-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    width: 100%;
}

.social-btn {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 12px;
    background: transparent;
    color: #111;
    border: 1.5px solid var(--surface-border);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.social-btn:hover {
    background: rgba(0, 0, 0, 0.04);
    border-color: #111;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.social-btn:active {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

/* Sign Up Prompt with animation */
.signup-wrapper {
    margin-top: 1.5rem;
    text-align: center;
}

.signup-prompt {
    font-size: 0.9375rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.5;
    display: inline-block;
}

.signup-link {
    color: var(--button-primary-bg);
    text-decoration: none;
    font-weight: 700;
    position: relative;
    display: inline-block;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.signup-link::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    bottom: -2px;
    left: 0;
    background-color: var(--button-primary-bg);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.signup-link:hover {
    opacity: 0.8;
}

.signup-link:hover::after {
    transform: scaleX(1);
    transform-origin: left;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-title {
        font-size: 1.75rem;
    }

    .social-btn span {
        display: none;
    }

    .social-btn {
        padding: 0.875rem;
    }

    .social-btn i {
        margin: 0;
    }

    .back-button {
        font-size: 0.8125rem;
    }
}

@media (max-width: 480px) {
    .form-title {
        font-size: 1.5rem;
    }

    .submit-btn {
        padding: 0.875rem 1rem;
    }
}

/* Dark theme */
:root[data-theme='dark'] .form-title,
:root[data-theme='dark'] .form-label,
:root[data-theme='dark'] .forgot-link,
:root[data-theme='dark'] .submit-btn,
:root[data-theme='dark'] .social-btn,
:root[data-theme='dark'] .signup-link {
    color: #111;
}

:root[data-theme='dark'] .social-btn:hover {
    background: rgba(0, 0, 0, 0.04);
}
</style>
