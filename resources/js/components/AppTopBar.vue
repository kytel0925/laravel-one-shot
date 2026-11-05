<script setup lang="ts">
import AppBreadcrumbs from '@/components/AppBreadcrumbs.vue';
import UserMenu from '@/components/UserMenu.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{ breadcrumbs: BreadcrumbItem[] }>();

const emit = defineEmits<{ toggleRail: [] }>();
</script>

<template>
    <v-app-bar color="surface" border="b" density="comfortable">
        <template #prepend>
            <v-app-bar-nav-icon
                aria-label="Toggle navigation drawer"
                data-test="drawer-toggle"
                @click="emit('toggleRail')"
            />
        </template>

        <v-app-bar-title>
            <AppBreadcrumbs
                v-if="breadcrumbs.length > 0"
                :breadcrumbs="breadcrumbs"
            />
        </v-app-bar-title>

        <template #append>
            <UserMenu class="me-2" />
        </template>
    </v-app-bar>
</template>
