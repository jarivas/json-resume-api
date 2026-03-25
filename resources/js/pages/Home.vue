<script setup lang="ts">
const now = new Date().toLocaleString();
</script>

<template>
    <main class="home-page">
        <h1>Vue + Inertia is ready</h1>
        <p>
            Frontend bootstrap completed successfully. You can now add real
            pages and layouts.
        </p>
        <small>Build generated at: {{ now }}</small>
    </main>
</template>

<style scoped>
.home-page {
    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto,
        Helvetica, Arial, sans-serif;
    margin: 3rem auto;
    max-width: 42rem;
    padding: 0 1rem;
    color: #0f172a;
}

h1 {
    margin: 0 0 0.75rem;
    font-size: 2rem;
    line-height: 1.15;
}

p {
    margin: 0 0 0.5rem;
    line-height: 1.6;
}

small {
    color: #475569;
}
</style>