<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { VBtn } from 'vuetify/components';
import AppLogo from '@/components/AppLogo.vue';
import { dashboard, login, register } from '@/routes';

const resources = [
    {
        title: 'Read the documentation',
        subtitle: 'Laravel has an incredibly rich ecosystem.',
        href: 'https://laravel.com/docs',
        icon: 'mdi-book-open-variant-outline',
    },
    {
        title: 'Watch video tutorials at Laracasts',
        subtitle: 'Screencasts on Laravel, PHP, Vue and more.',
        href: 'https://laracasts.com',
        icon: 'mdi-play-circle-outline',
    },
    {
        title: 'Browse the Vuetify components',
        subtitle: 'Material Design building blocks for every screen.',
        href: 'https://vuetifyjs.com/en/components/all/',
        icon: 'mdi-vuetify',
    },
];

const stack = [
    'Laravel 13',
    'Inertia 3',
    'Vue 3',
    'Vuetify 4',
    'Octane + FrankenPHP',
    'PostgreSQL',
    'Redis',
    'RabbitMQ',
];
</script>

<template>
    <Head title="Welcome" />

    <v-app-bar color="background" flat>
        <div class="page-container d-flex align-center">
            <AppLogo />
            <v-spacer />
            <nav class="d-flex ga-2" aria-label="Account">
                <Link
                    v-if="$page.props.auth.user"
                    :as="VBtn"
                    :href="dashboard()"
                    variant="tonal"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link :as="VBtn" :href="login()" variant="text"
                        >Log in</Link
                    >
                    <Link
                        :as="VBtn"
                        :href="register()"
                        color="primary"
                        variant="flat"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </div>
    </v-app-bar>

    <v-main class="bg-background">
        <div class="page-container py-8">
            <div class="welcome-grid">
                <div>
                    <v-card
                        color="surface-container-low"
                        rounded="xl"
                        height="100%"
                        flat
                    >
                        <v-card-item class="pt-6 px-6">
                            <v-card-title tag="h1" class="text-headline-small">
                                Let's get started
                            </v-card-title>
                            <v-card-subtitle class="text-body-large text-wrap">
                                We suggest starting with the following.
                            </v-card-subtitle>
                        </v-card-item>

                        <v-list bg-color="transparent" class="px-2" lines="two">
                            <v-list-item
                                v-for="item in resources"
                                :key="item.href"
                                :href="item.href"
                                target="_blank"
                                rel="noopener"
                                :prepend-icon="item.icon"
                                append-icon="mdi-open-in-new"
                                :title="item.title"
                                :subtitle="item.subtitle"
                                rounded="xl"
                            />
                        </v-list>

                        <v-card-actions class="px-6 pb-6">
                            <v-btn
                                href="https://cloud.laravel.com"
                                target="_blank"
                                rel="noopener"
                                variant="flat"
                                prepend-icon="mdi-rocket-launch-outline"
                            >
                                Deploy now
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </div>

                <div>
                    <v-card
                        color="primary-container"
                        rounded="xl"
                        height="100%"
                        flat
                    >
                        <v-card-item class="pt-6 px-6">
                            <v-card-title tag="h2" class="text-title-large">
                                What's inside
                            </v-card-title>
                            <v-card-subtitle class="text-body-medium text-wrap">
                                A Docker-first template with Material Design UI.
                            </v-card-subtitle>
                        </v-card-item>
                        <v-card-text class="d-flex flex-wrap ga-2 px-6 pb-6">
                            <v-chip
                                v-for="item in stack"
                                :key="item"
                                variant="outlined"
                            >
                                {{ item }}
                            </v-chip>
                        </v-card-text>
                    </v-card>
                </div>
            </div>
        </div>
    </v-main>
</template>

<style scoped>
/* One column on compact screens, 7/5 from the 960px contract width. */
.welcome-grid {
    display: grid;
    gap: 24px;
}

@media (min-width: 960px) {
    .welcome-grid {
        grid-template-columns: 7fr 5fr;
    }
}
</style>
