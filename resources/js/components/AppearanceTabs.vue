<script setup lang="ts">
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

const { appearance, updateAppearance } = useAppearance();

const selected = computed<Appearance>({
    get: () => appearance.value,
    set: (value) => {
        if (value) {
            updateAppearance(value);
        }
    },
});

const tabs = [
    { value: 'light', icon: 'mdi-white-balance-sunny', label: 'Light' },
    { value: 'dark', icon: 'mdi-weather-night', label: 'Dark' },
    { value: 'system', icon: 'mdi-monitor', label: 'System' },
] as const;
</script>

<template>
    <v-btn-toggle
        v-model="selected"
        mandatory
        divided
        variant="outlined"
        rounded="xl"
        color="secondary"
    >
        <v-btn
            v-for="tab in tabs"
            :key="tab.value"
            :value="tab.value"
            :prepend-icon="tab.icon"
        >
            {{ tab.label }}
        </v-btn>
    </v-btn-toggle>
</template>
