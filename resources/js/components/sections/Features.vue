<script setup>
import Card from 'primevue/card';
const iconList = [
    'pi pi-bolt',
    'pi pi-star',
    'pi pi-cog',
    'pi pi-globe',
    'pi pi-users',
    'pi pi-lock',
];
</script>

<template>
    <section class="features-section">
        <div class="features-header">
            <h2 class="features-title">Features</h2>
            <p class="features-subtitle">Discover what makes our product stand out</p>
        </div>
        <div class="features-grid">
            <Card v-for="n in 6" :key="n" class="feature-card">
                <template #title>
                    <span class="feature-icon">
                        <i :class="iconList[n-1]"></i>
                    </span>
                    Feature {{ n }}
                </template>
                <template #content>
                    <p class="m-0">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur error repudiandae numquam deserunt quisquam repellat libero asperiores earum nam nobis, culpa ratione quam perferendis esse, cupiditate neque quas!
                    </p>
                </template>
            </Card>
        </div>
    </section>
</template>

<style scoped>
.features-section {
    width: 100%;
    padding: 2rem 0;
}

.features-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.features-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: -1px;
}

.features-subtitle {
    font-size: 1.2rem;
    color: #64748b;
    margin: 0.5rem 0 0 0;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    padding: 2rem 150px;
}

.feature-card {
    min-height: 220px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.feature-icon {
    font-size: 2rem;
    color: #3b82f6;
    margin-right: 0.75rem;
    vertical-align: middle;
}

@media (max-width: 1200px) {
    .features-grid {
        padding: 2rem 2rem;
    }
}
@media (max-width: 900px) {
    .features-grid {
        grid-template-columns: 1fr 1fr;
        padding: 2rem 1rem;
    }
    .features-title {
        font-size: 2rem;
    }
}
@media (max-width: 600px) {
    .features-grid {
        grid-template-columns: 1fr;
        padding: 1rem 0.5rem;
    }
    .features-title {
        font-size: 1.5rem;
    }
    .features-header {
        margin-bottom: 1.5rem;
    }
}
</style>


