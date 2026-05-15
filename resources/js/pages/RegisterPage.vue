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
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'student',
    agreeToTerms: false
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.name || !values.name.trim()) {
        errors.name = [{ message: 'Full name is required.' }];
    }

    if (!values.email || !values.email.trim()) {
        errors.email = [{ message: 'Email is required.' }];
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
        errors.email = [{ message: 'Please enter a valid email address.' }];
    }

    if (!values.password) {
        errors.password = [{ message: 'Password is required.' }];
    } else if (values.password.length < 8) {
        errors.password = [{ message: 'Password must be at least 8 characters.' }];
    }

    if (!values.confirmPassword) {
        errors.confirmPassword = [{ message: 'Please confirm your password.' }];
    } else if (values.password !== values.confirmPassword) {
        errors.confirmPassword = [{ message: 'Passwords do not match.' }];
    }

    if (values.role !== 'teacher' && values.role !== 'student') {
        errors.role = [{ message: 'Please select your role.' }];
    }

    if (!values.agreeToTerms) {
        errors.agreeToTerms = [{ message: 'You must agree to the terms and conditions.' }];
    }

    return { errors };
};

const onFormSubmit = (e) => {
    if (e.valid) {
        console.log('Registration data:', {
            name: initialValues.value.name,
            email: initialValues.value.email,
            password: initialValues.value.password,
            role: initialValues.value.role
        });
        // Handle registration logic here
    }
};
</script>

<template>
    <div class="register-form-wrapper">
        <!-- Back to Homepage -->
        <button class="back-button" @click="router.push('/')">
            <i class="pi pi-arrow-left"></i>
            Back to Homepage
        </button>

        <div class="form-header">
            <h2 class="form-title">Create your account</h2>
            <p class="form-subtitle">Get started with your free account today.</p>
        </div>

        <Form
            v-slot="$form"
            :resolver="resolver"
            :initialValues="initialValues"
            @submit="onFormSubmit"
            class="register-form"
        >
            <!-- Role Selector -->
            <div class="form-field">
                <label class="form-label">I am a</label>
                <div class="role-selector">
                    <label 
                        class="role-option" 
                        :class="{ active: initialValues.role === 'teacher' }"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="teacher"
                            v-model="initialValues.role"
                            class="hidden-radio"
                        />
                        <div class="role-content">
                            <i class="pi pi-chalkboard-teacher"></i>
                            <span>Teacher</span>
                        </div>
                    </label>
                    <label 
                        class="role-option" 
                        :class="{ active: initialValues.role === 'student' }"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="student"
                            v-model="initialValues.role"
                            class="hidden-radio"
                        />
                        <div class="role-content">
                            <i class="pi pi-user-graduate"></i>
                            <span>Student</span>
                        </div>
                    </label>
                </div>
                <Message
                    v-if="$form.role?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.role.error?.message }}
                </Message>
            </div>

            <div class="form-field">
                <label for="name" class="form-label">Full Name</label>
                <InputText
                    id="name"
                    name="name"
                    type="text"
                    placeholder="Enter your full name"
                    class="form-input"
                />
                <Message
                    v-if="$form.name?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.name.error?.message }}
                </Message>
            </div>

            <div class="form-field">
                <label for="email" class="form-label">Email Address</label>
                <InputText
                    id="email"
                    name="email"
                    type="email"
                    placeholder="Enter your email"
                    class="form-input"
                />
                <Message
                    v-if="$form.email?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.email.error?.message }}
                </Message>
            </div>

            <div class="form-field">
                <label for="password" class="form-label">Password</label>
                <Password
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Create a password"
                    :feedback="true"
                    :weakLabel="'Weak'"
                    :mediumLabel="'Medium'"
                    :strongLabel="'Strong'"
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

            <div class="form-field">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <Password
                    id="confirmPassword"
                    name="confirmPassword"
                    type="password"
                    placeholder="Confirm your password"
                    :feedback="false"
                    class="form-input"
                    inputClass="w-full"
                />
                <Message
                    v-if="$form.confirmPassword?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.confirmPassword.error?.message }}
                </Message>
            </div>

            <div class="form-options">
                <div class="checkbox-field">
                    <Checkbox name="agreeToTerms" binary inputId="agreeToTerms" />
                    <label for="agreeToTerms" class="checkbox-label">
                        I agree to the <a href="#" class="link">Terms of Service</a> and <a href="#" class="link">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <Button
                type="submit"
                severity="secondary"
                label="Create Account"
                class="submit-btn"
            />
        </Form>

        <!-- Sign in prompt with smooth transition -->
        <div class="signin-wrapper">
            <p class="signin-prompt">
                Already have an account?
                <a 
                    href="/login" 
                    class="signin-link"
                    @click.prevent="router.push('/login')"
                >
                    Sign in
                </a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.register-form-wrapper {
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
.register-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

/* Role Selector */
.form-field:first-child {
    margin-bottom: 0.25rem;
}

.role-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    width: 100%;
}

.role-option {
    position: relative;
    cursor: pointer;
    border: 2px solid var(--surface-border);
    border-radius: 12px;
    padding: 0.5rem;
    transition: all 0.3s ease;
    background: var(--surface-bg);
}

.role-option:hover {
    border-color: var(--button-primary-bg);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.role-option.active {
    border-color: var(--button-primary-bg);
    background: var(--button-primary-bg);
}

.role-option.active .role-content {
    color: var(--button-primary-text);
}

.role-option.active .role-content i {
    color: var(--button-primary-text);
}

.hidden-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.role-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    color: var(--page-text);
    font-weight: 600;
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.role-content i {
    font-size: 1.25rem;
    color: var(--muted-text);
    transition: color 0.3s ease;
}

/* Regular Form Fields */
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
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
}

.checkbox-field {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.checkbox-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    margin: 0;
    user-select: none;
    line-height: 1.4;
}

.link {
    color: var(--button-primary-bg);
    text-decoration: none;
    font-weight: 600;
}

.link:hover {
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

/* Sign In Wrapper with smooth animation */
.signin-wrapper {
    margin-top: 1.5rem;
    text-align: center;
}

.signin-prompt {
    font-size: 0.9375rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.5;
    display: inline-block;
}

.signin-link {
    color: var(--button-primary-bg);
    text-decoration: none;
    font-weight: 700;
    position: relative;
    display: inline-block;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.signin-link::after {
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

.signin-link:hover {
    opacity: 0.8;
}

.signin-link:hover::after {
    transform: scaleX(1);
    transform-origin: left;
}

/* Responsive */
@media (max-width: 768px) {
    .form-title {
        font-size: 1.75rem;
    }

    .back-button {
        font-size: 0.8125rem;
    }

    .role-selector {
        gap: 0.5rem;
    }

    .role-content span {
        font-size: 0.875rem;
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
:root[data-theme='dark'] .signin-link,
:root[data-theme='dark'] .link {
    color: #111;
}

:root[data-theme='dark'] .submit-btn:hover,
:root[data-theme='dark'] .link:hover {
    opacity: 0.7;
}
</style>
