<script setup>
import { reactive } from 'vue';
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

    if (!values.course || !values.course.trim()) {
        errors.course = [{ message: 'Course is required.' }];
    }
    if (!values.addressLine || !values.addressLine.trim()) {
        errors.addressLine = [{ message: 'Street address is required.' }];
    }

    if (!values.barangay || !values.barangay.trim()) {
        errors.barangay = [{ message: 'Barangay is required.' }];
    }

    if (!values.cityMunicipality || !values.cityMunicipality.trim()) {
        errors.cityMunicipality = [{ message: 'City / Municipality is required.' }];
    }

    if (!values.province || !values.province.trim()) {
        errors.province = [{ message: 'Province is required.' }];
    }

    if (!values.region) {
        errors.region = [{ message: 'Region is required.' }];
    }

    if (!values.postalCode || !values.postalCode.trim()) {
        errors.postalCode = [{ message: 'Postal code is required.' }];
    } else if (!/^\d{4}$/.test(values.postalCode)) {
        errors.postalCode = [{ message: 'Enter a valid 4-digit postal code.' }];
    }

    if (!values.agreeToTerms) {
        errors.agreeToTerms = [{ message: 'You must agree to the terms and conditions.' }];
    }

    return { errors };
};

const onFormSubmit = (e) => {
    if (e.valid) {
        console.log('Registration data:', initialValues);
        // Handle registration logic here
    }
};

const roleOptions = [
    { label: 'Teacher', value: 'teacher' },
    { label: 'Student', value: 'student' },
];

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
    { label: 'LLB – Bachelor of Laws', value: 'llb' },
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

            <!-- Account Fields -->
            <div class="form-row">
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
            </div>

            <!-- Password Fields -->
            <div class="form-row">
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
            </div>

            <div class="form-section-divider">
                <span>Course Information</span>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="course" class="form-label">Course</label>
                    <Select
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
                        v-if="$form.course?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.course.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="region" class="form-label">Region</label>
                    <InputText
                        id="region"
                        name="region"
                        type="text"
                        placeholder="e.g. NCR / Central Luzon"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.region?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.region.error?.message }}
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
                    name="addressLine"
                    type="text"
                    placeholder="e.g. 123 Rizal Street"
                    class="form-input"
                />
                <Message
                    v-if="$form.addressLine?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.addressLine.error?.message }}
                </Message>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="barangay" class="form-label">Barangay</label>
                    <InputText
                        id="barangay"
                        name="barangay"
                        type="text"
                        placeholder="e.g. Barangay San Jose"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.barangay?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.barangay.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="cityMunicipality" class="form-label">City / Municipality</label>
                    <InputText
                        id="cityMunicipality"
                        name="cityMunicipality"
                        type="text"
                        placeholder="e.g. Quezon City"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.cityMunicipality?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.cityMunicipality.error?.message }}
                    </Message>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="province" class="form-label">Province</label>
                    <InputText
                        id="province"
                        name="province"
                        type="text"
                        placeholder="e.g. Metro Manila"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.province?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.province.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <InputText
                        id="postalCode"
                        name="postalCode"
                        type="text"
                        placeholder="e.g. 1103"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.postalCode?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.postalCode.error?.message }}
                    </Message>
                </div>
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
    max-width: 480px;
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
    gap: 0.75rem;
    width: 100%;
}

/* Two-column row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
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

.form-input,
.form-select {
    width: 100%;
}

/* Native select — matches .p-inputtext */
.form-select {
    padding: 0.75rem 0.875rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 12px;
    background: var(--surface-bg);
    color: var(--page-text);
    font-size: 0.875rem;
    font-weight: 400;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    appearance: auto;
    -webkit-appearance: auto;
    cursor: pointer;
    min-height: 44px;
    line-height: 1.5;
}

.form-select:focus {
    outline: none;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

/* PrimeVue Select — override theme defaults to match InputText exactly */
:deep(.p-select) {
    width: 100%;
    min-height: 44px;
}

:deep(.p-select .p-select-label) {
    padding: 0.75rem 0.875rem !important;
    font-size: 0.875rem !important;
    font-weight: 400 !important;
    line-height: 1.5 !important;
    min-height: 44px !important;
    border-radius: 12px;
    background: var(--surface-bg) !important;
    border: 1.5px solid var(--surface-border) !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
    color: var(--page-text) !important;
    transition: all 0.2s ease !important;
}

:deep(.p-select:has(.p-select-label.p-placeholder) .p-select-label) {
    color: var(--muted-text) !important;
}

:deep(.p-select.p-filled .p-select-label),
:deep(.p-select:not(.p-variant-filled) .p-select-label) {
    color: var(--page-text) !important;
}

:deep(.p-select:focus-within .p-select-label),
:deep(.p-select.p-focus .p-select-label) {
    border-color: #111 !important;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08) !important;
    background: #fff !important;
    outline: none !important;
}

:deep(.p-select-label:focus) {
    border-color: #111 !important;
}

/* Remove inner input that breaks layout */
:deep(.p-select .p-hidden-accessible),
:deep(.p-select .p-hidden-accessible input) {
    display: none !important;
}

:deep(.p-select.p-invalid .p-select-label) {
    border-color: #e53935 !important;
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
        max-width: 400px;
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
