<script setup>
import { ref } from 'vue';
import Carousel from 'primevue/carousel';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import Divider from 'primevue/divider';
import { testimonials } from '../../data/siteData';

const activeIndex = ref(0);
</script>

<template>
  <section id="testimonials" class="content-section testimonials-section">
    <div class="testimonials-container" v-reveal>
      <h2 class="section-title">What Our Users Say</h2>

      <Carousel
        :value="testimonials"
        :numVisible="1"
        :numScroll="1"
        circular
        :autoplayInterval="5000"
        class="testimonial-carousel"
        containerClass="carousel-container"
      >
        <template #item="slotProps">
          <Card class="testimonial-slide-card">
            <template #content>
              <div class="testimonial-inner">
                <div class="testimonial-avatar">
                  <Avatar
                    :image="slotProps.data.avatar"
                    :alt="slotProps.data.name"
                    size="xlarge"
                    shape="circle"
                  />
                </div>
                <p class="testimonial-text">{{ slotProps.data.text }}</p>
                <Divider class="testimonial-hr" />
                <div class="testimonial-footer">
                  <span class="testimonial-name">{{ slotProps.data.name }}</span>
                  <span class="testimonial-role">{{ slotProps.data.role }}</span>
                </div>
              </div>
            </template>
          </Card>
        </template>
      </Carousel>

      <div class="carousel-dots">
        <button
          v-for="t in testimonials"
          :key="t.id"
          type="button"
          class="carousel-dot"
          :aria-label="t.name"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.testimonials-section {
  background: var(--content-bg);
}

.testimonials-container {
  width: 100%;
  max-width: 720px;
  margin: 0 auto;
  text-align: center;
}

.section-title {
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--page-text);
  margin: 0 0 2.5rem 0;
  letter-spacing: -0.5px;
}

.testimonial-carousel :deep(.p-carousel-content) {
  overflow: hidden;
}

.testimonial-carousel :deep(.p-carousel-indicator-list) {
  display: none !important;
}

.carousel-container {
  padding: 0 0.25rem;
}

.testimonial-slide-card {
  border-radius: 8px;
  overflow: hidden;
  background: var(--surface-bg);
  box-shadow: 0 8px 40px rgba(0,0,0,0.07);
}

.testimonial-slide-card :deep(.p-card-body) {
  padding: 0;
}

.testimonial-slide-card :deep(.p-card-content) {
  padding: 2.5rem 2rem 2rem;
}

.testimonial-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.125rem;
}

.testimonial-avatar {
  width: 88px;
  height: 88px;
  flex-shrink: 0;
}

.testimonial-avatar :deep(.p-avatar) {
  border: 3px solid var(--surface-border);
}

.testimonial-text {
  font-size: 1.0625rem;
  line-height: 1.7;
  color: var(--muted-text);
  font-style: italic;
  margin: 0;
  text-align: center;
  max-width: 560px;
}

.testimonial-hr {
  width: 45%;
  border-style: dashed;
  border-color: var(--surface-border);
  margin: 0.125rem 0;
}

.testimonial-footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.375rem;
  width: 100%;
}

.testimonial-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--page-text);
  margin: 0;
}

.testimonial-role {
  font-size: 0.875rem;
  color: var(--muted-text);
  margin: 0;
}

.carousel-dots {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.625rem;
  margin-top: 1.75rem;
}

.carousel-dot {
  width: 11px;
  height: 11px;
  border-radius: 50%;
  border: 1.5px solid var(--surface-border);
  background: var(--surface-bg);
  cursor: pointer;
  padding: 0;
  outline: none;
  transition: background 0.3s ease, border-color 0.3s ease;
}

.carousel-dot:hover {
  border-color: color-mix(in srgb, var(--button-primary-bg) 50%, var(--surface-border) 50%);
}

.carousel-dot:focus-visible {
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--button-primary-bg) 15%, transparent);
}

@media (max-width: 768px) {
  .section-title {
    font-size: 1.875rem;
    margin-bottom: 2rem;
  }

  .testimonial-text {
    font-size: 1rem;
    line-height: 1.65;
    text-align: left;
  }

  .testimonial-slide-card :deep(.p-card-content) {
    padding: 2rem 1.5rem 1.5rem;
  }

  .testimonial-avatar {
    width: 72px;
    height: 72px;
  }
}

@media (max-width: 480px) {
  .section-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .testimonial-slide-card :deep(.p-card-content) {
    padding: 1.5rem 1.25rem 1.25rem;
  }

  .testimonial-avatar {
    width: 64px;
    height: 64px;
  }

  .testimonial-text {
    font-size: 0.9375rem;
    text-align: left;
  }

  .carousel-dot {
    width: 9px;
    height: 9px;
  }
}
</style>
