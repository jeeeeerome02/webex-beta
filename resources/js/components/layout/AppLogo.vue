<script setup>
import logo from '../../assets/images/logo.png';

defineProps({
  size: {
    type: Number,
    default: 100
  },

  textSize: {
    type: Number,
    default: 26
  },

  textColor: {
    type: String,
    default: null
  }
});
</script>

<template>
    <span class="app-logo-wrap" aria-label="Webex">
        <span
          class="logo-mark"
          aria-hidden="true"
          :style="{
            width: size + 'px',
            height: size + 'px',
            display: 'inline-flex',
            alignItems: 'center',
            justifyContent: 'center'
          }"
        >
            <img
              :src="logo"
              alt=""
              class="app-logo"
              :style="{
                maxWidth: size + 'px',
                maxHeight: size + 'px',
                width: '100%',
                height: '100%',
                objectFit: 'contain',
                display: 'block',
                margin: 0,
              }"
            />
        </span>

        <span
          class="logo-text"
          :style="{
            fontSize: textSize + 'px',
            marginLeft: 0,
            color: textColor || 'var(--navbar-text)'
          }"
        >
          webex
        </span>
    </span>
</template>

<style scoped>
.app-logo-wrap {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.logo-mark {
  display: inline-flex;
  overflow: visible !important;
}

.app-logo {
  width: auto;
  max-width: 100%;
  transform: none;
  display: block;
}

.logo-text {
    font-weight: 600;
    line-height: 1;
}
</style>
