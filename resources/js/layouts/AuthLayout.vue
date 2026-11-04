<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { VBtn } from 'vuetify/components';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import type { AuthLink } from '@/types';

defineOptions({ inheritAttrs: false });

/**
 * Props come from each auth page through `defineOptions({ layout: {...} })`.
 * The page slot holds the tonal alerts first and the full-width primary
 * button last.
 */
const {
    title = '',
    description = '',
    icon,
    switchLink,
    footer,
    back,
} = defineProps<{
    title?: string;
    description?: string;
    /** An `mdi-*` icon shown above the title instead of the logo. */
    icon?: string;
    /** "Don't have an account? Sign up" style link under the form. */
    switchLink?: AuthLink;
    /** Secondary link at the bottom of the card ("Log out"). */
    footer?: AuthLink;
    /** Where the back arrow goes; defaults to home. */
    back?: AuthLink['href'];
}>();
</script>

<template>
    <v-main class="auth-main bg-surface-container">
        <div class="auth-wrapper">
            <v-card
                class="auth-card"
                color="surface"
                rounded="xl"
                elevation="0"
                border
            >
                <div class="pa-6 pa-sm-10">
                    <Link
                        :as="VBtn"
                        :href="back ?? home()"
                        class="auth-back"
                        icon="mdi-arrow-left"
                        variant="text"
                        color="on-surface-variant"
                        size="small"
                        aria-label="Back"
                        data-test="auth-back"
                    />

                    <div
                        class="d-flex flex-column align-center text-center ga-4 mb-8"
                    >
                        <v-avatar
                            v-if="icon"
                            color="secondary-container"
                            size="56"
                        >
                            <v-icon :icon="icon" size="28" />
                        </v-avatar>
                        <v-avatar v-else color="primary" rounded="lg" size="48">
                            <AppLogoIcon class="auth-logo" />
                        </v-avatar>

                        <div>
                            <h1 class="text-headline-small mb-2">
                                {{ title }}
                            </h1>
                            <p
                                v-if="description"
                                class="text-body-medium text-medium-emphasis"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <div class="d-flex flex-column ga-4">
                        <slot />
                    </div>

                    <p
                        v-if="switchLink"
                        class="text-body-medium text-center text-medium-emphasis mt-6"
                    >
                        {{ switchLink.text }}
                        <Link :href="switchLink.href" class="auth-link">
                            {{ switchLink.label }}
                        </Link>
                    </p>

                    <div v-if="footer" class="text-center mt-4">
                        <Link
                            :href="footer.href"
                            class="auth-link text-body-medium"
                        >
                            {{ footer.label }}
                        </Link>
                    </div>
                </div>
            </v-card>
        </div>
    </v-main>
</template>

<style scoped>
.auth-wrapper {
    display: flex;
    min-height: 100%;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.auth-card {
    width: 100%;
    max-width: 448px;
}

.auth-back {
    margin: -8px 0 8px -8px;
}

.auth-logo {
    width: 28px;
    height: 28px;
}

.auth-link {
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
    text-decoration: none;
}

.auth-link:hover {
    text-decoration: underline;
}

@media (min-width: 600px) {
    .auth-wrapper {
        padding: 48px 24px;
    }
}
</style>
