<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

const handleLogin = () => {
  loading.value = true;
  error.value = '';
  // Simulate login (replace with real API call)
  setTimeout(() => {
    loading.value = false;
    if (!email.value || !password.value) {
      error.value = 'Email and password are required.';
    } else {
      // Success: redirect or update state
      error.value = '';
      // window.location.href = '/dashboard';
    }
  }, 1000);
};
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <h2 class="login-title">Sign In</h2>
      <form @submit.prevent="handleLogin">
        <div class="p-field">
          <label for="email">Email</label>
          <InputText id="email" v-model="email" type="email" placeholder="Enter your email" class="p-inputtext-lg w-full" />
        </div>
        <div class="p-field">
          <label for="password">Password</label>
          <Password id="password" v-model="password" toggleMask placeholder="Enter your password" class="w-full" inputClass="p-inputtext-lg w-full" />
        </div>
        <Button label="Login" icon="pi pi-sign-in" type="submit" class="w-full login-btn" :loading="loading" />
        <Message v-if="error" severity="error" class="mt-3">{{ error }}</Message>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--content-bg, #f9f9f9);
}
.login-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
  padding: 2.5rem 2rem 2rem 2rem;
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.login-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2rem;
  color: var(--page-text, #222);
}
.p-field {
  width: 100%;
  margin-bottom: 1.5rem;
}
.login-btn {
  margin-top: 0.5rem;
  font-size: 1.1rem;
}
.mt-3 {
  margin-top: 1.5rem;
}
@media (max-width: 600px) {
  .login-card {
    padding: 1.5rem 0.5rem 1rem 0.5rem;
    max-width: 98vw;
  }
  .login-title {
    font-size: 1.3rem;
  }
}
</style>
