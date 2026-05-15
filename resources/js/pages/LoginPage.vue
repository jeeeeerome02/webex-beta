<script setup>
import { ref } from 'vue';
import { Form } from '@primevue/forms';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';
import AppLogo from '../components/layout/AppLogo.vue';

import loginBg from '../assets/images/login-background.jpg';

const initialValues = ref({
    username: '',
    password: ''
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.username) {
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
    <div class="login-page">

        <!-- TOP IMAGE -->
        <div
            class="login-header"
            :style="{ backgroundImage: `url(${loginBg})` }"
        ></div>

        <!-- CENTER LOGIN CARD -->
        <div class="login-content">
            <div class="login-card">

                <div class="mb-3 login-logo">
                    <AppLogo :size="90" :textSize="44" />
                </div>

                <h2 class="login-title">Sign In</h2>

                <Form
                    v-slot="$form"
                    :resolver="resolver"
                    :initialValues="initialValues"
                    @submit="onFormSubmit"
                    class="flex justify-center flex-col gap-4 w-full"
                >
                    <div class="flex flex-col gap-1 w-full">
                        <InputText
                            name="username"
                            type="text"
                            placeholder="Username"
                            class="p-inputtext-lg w-full"
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

                    <div class="flex flex-col gap-1 w-full">
                        <InputText
                            name="password"
                            type="password"
                            placeholder="Password"
                            class="p-inputtext-lg w-full"
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

                    <Button
                        type="submit"
                        severity="secondary"
                        label="Login"
                        class="w-full mt-2"
                    />
                </Form>

            </div>
        </div>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    background: #f5f5f5;
    position: relative;
}

/* TOP IMAGE */
.login-header {
    height: 52vh;
    width: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* CENTER CONTENT */
.login-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 1rem;
}

/* LOGIN CARD */
.login-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 420px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* TITLE */
.login-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: #222;
}

/* MOBILE */
@media (max-width: 768px) {
    .login-header {
        height: 30vh;
    }

    .login-card {
        padding: 2rem 1.2rem;
    }

    .login-title {
        font-size: 1.5rem;
    }
}
</style>
