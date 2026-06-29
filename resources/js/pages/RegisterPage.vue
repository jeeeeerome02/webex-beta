<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { Form } from '@primevue/forms';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Checkbox from 'primevue/checkbox';
import { useRouter } from 'vue-router';

const router = useRouter();

const initialValues = reactive({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'student',
    course: '',
    // Philippine address fields
    addressLine: '',
    barangay: '',
    cityMunicipality: '',
    province: '',
    region: '',
    postalCode: '',
    agreeToTerms: false
});

const isSubmitting = ref(false);
const serverError = ref('');
const serverErrors = ref({});

const resolver = ({ values }) => {
    const formValues = { ...initialValues, ...values };
    const errors = {};

    if (!formValues.name || !formValues.name.trim()) {
        errors.name = [{ message: 'Full name is required.' }];
    }

    if (!formValues.email || !formValues.email.trim()) {
        errors.email = [{ message: 'Email is required.' }];
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formValues.email)) {
        errors.email = [{ message: 'Please enter a valid email address.' }];
    }

    if (!formValues.password) {
        errors.password = [{ message: 'Password is required.' }];
    } else if (formValues.password.length < 8) {
        errors.password = [{ message: 'Password must be at least 8 characters.' }];
    }

    if (!formValues.confirmPassword) {
        errors.confirmPassword = [{ message: 'Please confirm your password.' }];
    } else if (formValues.password !== formValues.confirmPassword) {
        errors.confirmPassword = [{ message: 'Passwords do not match.' }];
    }

    if (formValues.role !== 'teacher' && formValues.role !== 'student') {
        errors.role = [{ message: 'Please select your role.' }];
    }

    if (!formValues.course || !formValues.course.trim()) {
        errors.course = [{ message: 'Course is required.' }];
    }
    if (!formValues.addressLine || !formValues.addressLine.trim()) {
        errors.addressLine = [{ message: 'Street address is required.' }];
    }

    if (!formValues.barangay || !formValues.barangay.trim()) {
        errors.barangay = [{ message: 'Barangay is required.' }];
    }

    if (!formValues.cityMunicipality || !formValues.cityMunicipality.trim()) {
        errors.cityMunicipality = [{ message: 'City / Municipality is required.' }];
    }

    if (!formValues.province || !formValues.province.trim()) {
        errors.province = [{ message: 'Province is required.' }];
    }

    if (!formValues.region) {
        errors.region = [{ message: 'Region is required.' }];
    }

    if (!formValues.postalCode || !formValues.postalCode.trim()) {
        errors.postalCode = [{ message: 'Postal code is required.' }];
    } else if (!/^\d{4}$/.test(formValues.postalCode)) {
        errors.postalCode = [{ message: 'Enter a valid 4-digit postal code.' }];
    }

    if (!formValues.agreeToTerms) {
        errors.agreeToTerms = [{ message: 'You must agree to the terms and conditions.' }];
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
        const { data } = await axios.post('/register', { ...initialValues });
        window.dispatchEvent(new CustomEvent('auth:changed', { detail: data.user }));
        await router.push('/dashboard');
    } catch (error) {
        if (error.response?.status === 422) {
            serverErrors.value = error.response.data.errors || {};
            serverError.value = error.response.data.message || 'Please check the registration form.';
            return;
        }

        serverError.value = 'Unable to create your account right now. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};

const philippineCourses = [
    { label: 'Select Course', value: '' },
    { label: 'BS Information Technology', value: 'bsit' },
    { label: 'BS Computer Science', value: 'bscs' },
    { label: 'BS Information Systems', value: 'bsis' },
    { label: 'BS Software Engineering', value: 'bsse' },
    { label: 'BS Data Science', value: 'bsds' },
    { label: 'BS Cybersecurity', value: 'bscscy' },
    { label: 'BS Business Administration', value: 'bsba' },
    { label: 'BS Accountancy', value: 'bsa' },
    { label: 'BS Marketing', value: 'bsmkt' },
    { label: 'BS Economics', value: 'bse' },
    { label: 'BS Psychology', value: 'bsp' },
    { label: 'BS Nursing', value: 'bsn' },
    { label: 'BS Education', value: 'bsed' },
    { label: 'BS Engineering', value: 'bse' },
    { label: 'BA Communication', value: 'bacomm' },
    { label: 'BA Political Science', value: 'baps' },
    { label: 'BS Hospitality Management', value: 'bshm' },
    { label: 'BS Tourism Management', value: 'bstm' },
    { label: 'BS Architecture', value: 'bsa' },
    { label: 'BS Civil Engineering', value: 'bsce' },
    { label: 'BS Electrical Engineering', value: 'bsee' },
    { label: 'BS Mechanical Engineering', value: 'bsme' },
    { label: 'BS Electronics Engineering', value: 'bsece' },
    { label: 'BS Accountancy', value: 'bsa' },
    { label: 'BS Entrepreneurship', value: 'bse' },
    { label: 'Master of Business Administration', value: 'mba' },
    { label: 'Master of Education', value: 'med' },
    { label: 'Doctor of Medicine', value: 'md' },
    { label: 'LLB - Bachelor of Laws', value: 'llb' },
    { label: 'Other', value: 'other' },
];

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
                            <i class="pi pi-book"></i>
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
                            <i class="pi pi-graduation-cap"></i>
                            <span>Student</span>
                        </div>
                    </label>
                </div>
                <Message
                    v-if="$form.role?.invalid || fieldError('role')"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.role?.error?.message || fieldError('role') }}
                </Message>
            </div>

            <!-- Account Fields -->
            <div class="form-row">
                <div class="form-field">
                    <label for="name" class="form-label">Full Name</label>
                    <InputText
                        id="name"
                        v-model="initialValues.name"
                        name="name"
                        type="text"
                        autocomplete="name"
                        placeholder="Enter your full name"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.name?.invalid || fieldError('name')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.name?.error?.message || fieldError('name') }}
                    </Message>
                </div>

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
            </div>

            <!-- Password Fields -->
            <div class="form-row">
                <div class="form-field">
                    <label for="password" class="form-label">Password</label>
                    <Password
                        id="password"
                        v-model="initialValues.password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Create a password"
                        :feedback="true"
                        :weakLabel="'Weak'"
                        :mediumLabel="'Medium'"
                        :strongLabel="'Strong'"
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

                <div class="form-field">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <Password
                        id="confirmPassword"
                        v-model="initialValues.confirmPassword"
                        name="confirmPassword"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Confirm your password"
                        :feedback="false"
                        class="form-input"
                        inputClass="w-full"
                    />
                    <Message
                        v-if="$form.confirmPassword?.invalid || fieldError('confirmPassword')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.confirmPassword?.error?.message || fieldError('confirmPassword') }}
                    </Message>
                </div>
            </div>

            <div class="form-section-divider">
                <span>Course Information</span>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="course" class="form-label">Course</label>
                    <Select
                        inputId="course"
                        name="course"
                        v-model="initialValues.course"
                        :options="philippineCourses"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select your course"
                        class="form-select"
                        :highlightOnSelect="false"
                        showClear
                        filter
                    />
                    <Message
                        v-if="$form.course?.invalid || fieldError('course')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.course?.error?.message || fieldError('course') }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="region" class="form-label">Region</label>
                    <InputText
                        id="region"
                        v-model="initialValues.region"
                        name="region"
                        type="text"
                        autocomplete="address-level1"
                        placeholder="e.g. NCR / Central Luzon"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.region?.invalid || fieldError('region')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.region?.error?.message || fieldError('region') }}
                    </Message>
                </div>
            </div>

            <div class="form-section-divider">
                <span>Address Details</span>
            </div>

            <div class="form-field">
                <label for="addressLine" class="form-label">Street / Building Address</label>
                <InputText
                    id="addressLine"
                    v-model="initialValues.addressLine"
                    name="addressLine"
                    type="text"
                    autocomplete="street-address"
                    placeholder="e.g. 123 Rizal Street"
                    class="form-input"
                />
                <Message
                    v-if="$form.addressLine?.invalid || fieldError('addressLine')"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.addressLine?.error?.message || fieldError('addressLine') }}
                </Message>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="barangay" class="form-label">Barangay</label>
                    <InputText
                        id="barangay"
                        v-model="initialValues.barangay"
                        name="barangay"
                        type="text"
                        placeholder="e.g. Barangay San Jose"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.barangay?.invalid || fieldError('barangay')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.barangay?.error?.message || fieldError('barangay') }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="cityMunicipality" class="form-label">City / Municipality</label>
                    <InputText
                        id="cityMunicipality"
                        v-model="initialValues.cityMunicipality"
                        name="cityMunicipality"
                        type="text"
                        autocomplete="address-level2"
                        placeholder="e.g. Quezon City"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.cityMunicipality?.invalid || fieldError('cityMunicipality')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.cityMunicipality?.error?.message || fieldError('cityMunicipality') }}
                    </Message>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="province" class="form-label">Province</label>
                    <InputText
                        id="province"
                        v-model="initialValues.province"
                        name="province"
                        type="text"
                        autocomplete="address-level1"
                        placeholder="e.g. Metro Manila"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.province?.invalid || fieldError('province')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.province?.error?.message || fieldError('province') }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <InputText
                        id="postalCode"
                        v-model="initialValues.postalCode"
                        name="postalCode"
                        type="text"
                        inputmode="numeric"
                        autocomplete="postal-code"
                        placeholder="e.g. 1103"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.postalCode?.invalid || fieldError('postalCode')"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.postalCode?.error?.message || fieldError('postalCode') }}
                    </Message>
                </div>
            </div>

            <div class="form-options">
                <div class="checkbox-field">
                    <Checkbox v-model="initialValues.agreeToTerms" name="agreeToTerms" binary inputId="agreeToTerms" />
                    <label for="agreeToTerms" class="checkbox-label">
                        I agree to the <a href="#" class="link">Terms of Service</a> and <a href="#" class="link">Privacy Policy</a>
                    </label>
                </div>
                <Message
                    v-if="$form.agreeToTerms?.invalid || fieldError('agreeToTerms')"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.agreeToTerms?.error?.message || fieldError('agreeToTerms') }}
                </Message>
            </div>

            <Button
                type="submit"
                severity="secondary"
                label="Create Account"
                class="submit-btn"
                :loading="isSubmitting"
                :disabled="isSubmitting"
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
    max-width: 560px;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin: 0 auto;
    padding-block: 0.25rem;
}

/* Back Button */
.back-button {
    display: inline-flex;
    align-items: center;
    align-self: flex-start;
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

/* Two-column row */
.form-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

/* Section Divider */
.form-section-divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 0.75rem 0;
}

.form-section-divider::before,
.form-section-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--surface-border);
}

.form-section-divider span {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-text);
    white-space: nowrap;
}

/* Role Selector */
.form-field:first-child {
    margin-bottom: 0.25rem;
}

.role-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    width: 100%;
}

.role-option {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 68px;
    cursor: pointer;
    border: 2px solid var(--surface-border);
    border-radius: 8px;
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
    min-width: 0;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111;
    letter-spacing: 0.02em;
}

.form-input,
.form-select {
    width: 100%;
    min-width: 0;
}

/* PrimeVue Select - keep the root box aligned with InputText */
:deep(.p-select) {
    width: 100%;
    min-width: 0;
    min-height: 46px;
}

:deep(.p-select.form-select) {
    display: flex;
    align-items: center;
    border: 1.5px solid var(--surface-border);
    border-radius: 8px;
    background: var(--surface-bg);
    color: var(--page-text);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

:deep(.p-select.form-select .p-select-label) {
    flex: 1 1 auto;
    min-width: 0;
    padding: 0.75rem 0.875rem !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    color: var(--page-text) !important;
    font-size: 0.875rem !important;
    font-weight: 400 !important;
    line-height: 1.5 !important;
}

:deep(.p-select.form-select .p-select-dropdown) {
    width: 2.75rem;
    color: var(--muted-text);
}

:deep(.p-select.form-select:has(.p-select-label.p-placeholder) .p-select-label) {
    color: var(--muted-text) !important;
}

:deep(.p-select.form-select.p-focus),
:deep(.p-select.form-select:focus-within) {
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

:deep(.p-select.form-select.p-invalid) {
    border-color: #e53935;
}

:deep(.p-select-overlay) {
    max-width: min(560px, calc(100vw - 2rem));
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
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
}

.checkbox-field {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

:deep(.p-checkbox) {
    flex: 0 0 auto;
    margin-top: 0.125rem;
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
    .register-form-wrapper {
        max-width: 520px;
    }

    .form-title {
        font-size: 1.75rem;
    }

    .back-button {
        font-size: 0.8125rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .role-selector {
        grid-template-columns: 1fr 1fr;
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
