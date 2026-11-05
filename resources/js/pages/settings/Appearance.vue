<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Appearance settings" />

    <SettingsSection
        title="Appearance settings"
        description="Update the appearance settings for your account"
    >
        <div>
            <AppearanceTabs />
        </div>
    </SettingsSection>
</template>
