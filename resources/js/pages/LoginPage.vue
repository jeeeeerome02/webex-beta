<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { Form } from '@primevue/forms';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Checkbox from 'primevue/checkbox';
import { useRouter } from 'vue-router';

const router = useRouter();

const initialValues = reactive({
    email: '',
    password: '',
    remember: false
});

const isSubmitting = ref(false);
const serverError = ref('');
const serverErrors = ref({});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.email || !values.email.trim()) {
        errors.email = [{ message: 'Email is required.' }];
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
        errors.email = [{ message: 'Please enter a valid email address.' }];
    }

    if (!values.password) {
        errors.password = [{ message: 'Password is required.' }];
    }

    return { errors };
};

const fieldError = (field) => serverErrors.value[field]?.[0] || '';

const onFormSubmit = async (e) => {
    serverError.value = '';
    serverErrors.value = {};

    if (!e.valid || isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;

    try {
        const { data } = await axios.post('/login', {
            email: initialValues.email,
            password: initialValues.password,
            remember: initialValues.remember,
        });

        window.dispatchEvent(new CustomEvent('auth:changed', { detail: data.user }));
        await router.push('/');
    } catch (error) {
        if (error.response?.status === 422) {
            serverErrors.value = error.response.data.errors || {};
            serverError.value = error.response.data.message || 'Please check your sign in details.';
            return;
        }

        serverError.value = 'Unable to sign in right now. Please try again.';
    } finally {
        isSubmitting.value = false;
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

        <Message
            v-if="serverError"
            severity="error"
            size="small"
            class="form-alert"
        >
            {{ serverError }}
        </Message>

        <Form
            v-slot="$form"
            :resolver="resolver"
            :initialValues="initialValues"
            @submit="onFormSubmit"
            class="login-form"
        >
            <div class="form-field">
                <label for="email" class="form-label">Email Address</label>
                <InputText
                    id="email"
                    v-model="initialValues.email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="Enter your email"
                    class="form-input"
                />
                <Message
                    v-if="$form.email?.invalid || fieldError('email')"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.email?.error?.message || fieldError('email') }}
                </Message>
            </div>

            <div class="form-field">
                <label for="password" class="form-label">Password</label>
                <Password
                    id="password"
                    v-model="initialValues.password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    :feedback="false"
                    class="form-input"
                    inputClass="w-full"
                />
                <Message
                    v-if="$form.password?.invalid || fieldError('password')"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.password?.error?.message || fieldError('password') }}
                </Message>
            </div>

            <div class="form-options">
                <div class="checkbox-field">
                    <Checkbox v-model="initialValues.remember" name="remember" binary inputId="remember" />
                    <label for="remember" class="checkbox-label">Remember me</label>
                </div>
            </div>

            <Button
                type="submit"
                severity="secondary"
                label="Sign In"
                class="submit-btn"
                :loading="isSubmitting"
                :disabled="isSubmitting"
            />
        </Form>

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
    min-height: 46px;
    box-sizing: border-box;
    padding: 0.75rem 0.875rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 8px;
    background: var(--surface-bg);
    color: var(--page-text);
    font-size: 0.875rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

:deep(.p-password) {
    display: block;
    width: 100%;
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
    border-radius: 8px;
}

:deep(.p-message) {
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    margin-top: 0.25rem;
}

.form-alert {
    width: 100%;
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

:deep(.p-checkbox) {
    flex: 0 0 auto;
}

.checkbox-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    margin: 0;
    user-select: none;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 8px;
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

</style>
