<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: NonNullable<InertiaLinkProps['href']>;
    method?: InertiaLinkProps['method'];
    as?: string;
}>();
</script>

<template>
    <Link :href="href" :method="method" :as="as" class="text-link">
        <slot />
    </Link>
</template>

<style scoped>
.text-link {
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
    text-decoration: none;
    background: none;
    border: 0;
    padding: 0;
    cursor: pointer;
    font: inherit;
}

.text-link:hover,
.text-link:focus-visible {
    text-decoration: underline;
}
</style>
