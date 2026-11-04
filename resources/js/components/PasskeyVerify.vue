<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { usePasskeyVerify } from '@laravel/passkeys/vue';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

const props = defineProps<Props>();

const { verify, isLoading, error, isSupported } = usePasskeyVerify({
    ...(props.routes
        ? {
              routes: {
                  options: props.routes.options.url,
                  submit: props.routes.submit.url,
              },
          }
        : {}),
    onSuccess: (response) => {
        router.visit(response.redirect ?? '/dashboard');
    },
});
</script>

<template>
    <div v-if="isSupported" class="d-flex flex-column ga-4">
        <v-btn
            type="button"
            variant="outlined"
            color="primary"
            size="large"
            block
            prepend-icon="mdi-key-chain-variant"
            :loading="isLoading"
            :disabled="isLoading"
            @click="verify"
        >
            {{
                isLoading
                    ? (props.loadingLabel ?? 'Authenticating...')
                    : (props.label ?? 'Sign in with a passkey')
            }}
        </v-btn>

        <v-alert v-if="error" type="error">{{ error }}</v-alert>

        <div class="d-flex align-center ga-4">
            <v-divider />
            <span
                class="text-label-small text-medium-emphasis text-uppercase text-no-wrap"
            >
                {{ props.separator ?? 'Or continue with email' }}
            </span>
            <v-divider />
        </div>
    </div>
</template>
