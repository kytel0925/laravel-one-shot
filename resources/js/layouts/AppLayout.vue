<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import AppBottomNav from '@/components/AppBottomNav.vue';
import AppDrawer from '@/components/AppDrawer.vue';
import AppMobileBar from '@/components/AppMobileBar.vue';
import AppTopBar from '@/components/AppTopBar.vue';
import type { BreadcrumbItem } from '@/types';

defineOptions({ inheritAttrs: false });

const { breadcrumbs = [] } = defineProps<{ breadcrumbs?: BreadcrumbItem[] }>();

const { mdAndUp } = useDisplay();
const page = usePage();

// The drawer collapses to a rail; the choice is kept in the `sidebar_state`
// cookie so the server renders the same width (HandleInertiaRequests).
const rail = ref(!page.props.sidebarOpen);

watch(rail, (value) => {
    document.cookie = `sidebar_state=${!value};path=/;max-age=${60 * 60 * 24 * 7};SameSite=Lax`;
});
</script>

<template>
    <template v-if="mdAndUp">
        <AppDrawer v-model:rail="rail" />
        <AppTopBar :breadcrumbs="breadcrumbs" @toggle-rail="rail = !rail" />
    </template>
    <template v-else>
        <AppMobileBar :breadcrumbs="breadcrumbs" />
        <AppBottomNav />
    </template>

    <v-main class="bg-background">
        <div class="page-container py-6">
            <slot />
        </div>
    </v-main>
</template>
