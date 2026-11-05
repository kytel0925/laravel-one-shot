<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import UserMenu from '@/components/UserMenu.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs } = defineProps<{ breadcrumbs: BreadcrumbItem[] }>();

const title = computed(() => breadcrumbs.at(-1)?.title);

const emit = defineEmits<{ openDrawer: [] }>();
</script>

<template>
    <v-app-bar
        color="surface-container"
        density="comfortable"
        data-test="app-mobile-bar"
    >
        <template #prepend>
            <v-app-bar-nav-icon
                aria-label="Open navigation"
                data-test="mobile-drawer-toggle"
                @click="emit('openDrawer')"
            />
            <Link
                :href="dashboard()"
                class="mobile-home ms-2"
                aria-label="Home"
            >
                <AppLogo :show-name="!title" />
            </Link>
        </template>

        <v-app-bar-title v-if="title" class="text-title-large">
            {{ title }}
        </v-app-bar-title>

        <template #append>
            <UserMenu class="me-1" />
        </template>
    </v-app-bar>
</template>

<style scoped>
.mobile-home {
    color: inherit;
    text-decoration: none;
}
</style>
