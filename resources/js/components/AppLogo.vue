<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const { showName = true } = defineProps<{ showName?: boolean }>();

const name = usePage().props.name;
</script>

<template>
    <div class="app-logo d-flex align-center ga-3">
        <v-avatar color="primary" rounded="lg" size="36">
            <AppLogoIcon class="app-logo__icon" />
        </v-avatar>
        <span v-if="showName" class="text-title-medium text-truncate">
            {{ name }}
        </span>
    </div>
</template>

<style scoped>
.app-logo__icon {
    width: 20px;
    height: 20px;
}
</style>
