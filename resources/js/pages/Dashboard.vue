<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PageHeading from '@/components/PageHeading.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);

const placeholders = [
    { title: 'Metric one', icon: 'mdi-chart-line' },
    { title: 'Metric two', icon: 'mdi-chart-donut' },
    { title: 'Metric three', icon: 'mdi-chart-bar' },
];
</script>

<template>
    <Head title="Dashboard" />

    <PageHeading
        :title="`Welcome back, ${firstName}`"
        description="Your workspace is ready. Replace these placeholders with your first feature."
    />

    <v-row data-test="dashboard-cards">
        <v-col v-for="item in placeholders" :key="item.title" cols="12" sm="4">
            <v-card
                color="surface-container-low"
                rounded="xl"
                height="100%"
                flat
            >
                <v-card-item>
                    <template #prepend>
                        <v-avatar color="primary-container" rounded="lg">
                            <v-icon :icon="item.icon" />
                        </v-avatar>
                    </template>
                    <v-card-title class="text-title-medium">
                        {{ item.title }}
                    </v-card-title>
                    <v-card-subtitle>No data yet</v-card-subtitle>
                </v-card-item>
                <v-card-text>
                    <v-skeleton-loader
                        type="text@2"
                        boilerplate
                        color="transparent"
                    />
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>

    <v-card class="mt-6" color="surface-container-low" rounded="xl" flat>
        <v-empty-state
            icon="mdi-view-dashboard-outline"
            title="Nothing to show yet"
            text="Add an aggregate under src/ and surface its data here."
        />
    </v-card>
</template>
