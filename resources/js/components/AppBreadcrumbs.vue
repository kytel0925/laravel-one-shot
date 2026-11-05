<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

defineProps<{ breadcrumbs: BreadcrumbItem[] }>();
</script>

<template>
    <v-breadcrumbs class="pa-0" density="compact" aria-label="Breadcrumb">
        <template v-for="(item, index) in breadcrumbs" :key="index">
            <v-breadcrumbs-divider v-if="index > 0" />
            <v-breadcrumbs-item
                v-if="index === breadcrumbs.length - 1"
                :title="item.title"
                aria-current="page"
                class="text-title-medium"
            />
            <v-breadcrumbs-item v-else>
                <Link :href="item.href" class="breadcrumb-link">
                    {{ item.title }}
                </Link>
            </v-breadcrumbs-item>
        </template>
    </v-breadcrumbs>
</template>

<style scoped>
.breadcrumb-link {
    color: rgb(var(--v-theme-on-surface-variant));
    text-decoration: none;
}

.breadcrumb-link:hover {
    text-decoration: underline;
}
</style>
