<script setup lang="ts">
const { title, description } = defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <header class="mb-6">
        <h1 class="text-headline-small">{{ title }}</h1>
        <p
            v-if="description"
            class="text-body-medium text-medium-emphasis mt-1"
        >
            {{ description }}
        </p>
    </header>
</template>
