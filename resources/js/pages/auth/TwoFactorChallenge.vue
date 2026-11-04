<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watchEffect } from 'vue';
import { store } from '@/routes/two-factor/login';
import type { TwoFactorConfigContent } from '@/types';

defineOptions({
    layout: {
        icon: 'mdi-two-factor-authentication',
    },
});

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
            buttonText: 'login using an authentication code',
        };
    }

    return {
        title: 'Authentication code',
        description:
            'Enter the authentication code provided by your authenticator application.',
        buttonText: 'login using a recovery code',
    };
});

watchEffect(() => {
    setLayoutProps({
        title: authConfigContent.value.title,
        description: authConfigContent.value.description,
    });
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};
</script>

<template>
    <Head title="Two-factor authentication" />

    <Form
        v-if="!showRecoveryInput"
        v-bind="store.form()"
        class="d-flex flex-column ga-4"
        reset-on-error
        @error="code = ''"
        #default="{ errors, processing, clearErrors }"
    >
        <input type="hidden" name="code" :value="code" />

        <div>
            <v-otp-input
                v-model="code"
                length="6"
                type="number"
                label="Authentication code"
                :disabled="processing"
                :error="!!errors.code"
                autofocus
            />
            <p
                v-if="errors.code"
                class="text-body-medium text-error text-center mt-2"
            >
                {{ errors.code }}
            </p>
        </div>

        <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            :loading="processing"
            :disabled="processing"
        >
            Continue
        </v-btn>

        <div class="text-center text-body-medium text-medium-emphasis">
            <span>or you can </span>
            <v-btn
                type="button"
                variant="text"
                color="primary"
                size="small"
                @click="() => toggleRecoveryMode(clearErrors)"
            >
                {{ authConfigContent.buttonText }}
            </v-btn>
        </div>
    </Form>

    <Form
        v-else
        v-bind="store.form()"
        class="d-flex flex-column ga-4"
        reset-on-error
        #default="{ errors, processing, clearErrors }"
    >
        <v-text-field
            name="recovery_code"
            label="Recovery code"
            type="text"
            placeholder="Enter recovery code"
            :error-messages="errors.recovery_code"
            v-focus
            required
        />

        <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            :loading="processing"
            :disabled="processing"
        >
            Continue
        </v-btn>

        <div class="text-center text-body-medium text-medium-emphasis">
            <span>or you can </span>
            <v-btn
                type="button"
                variant="text"
                color="primary"
                size="small"
                @click="() => toggleRecoveryMode(clearErrors)"
            >
                {{ authConfigContent.buttonText }}
            </v-btn>
        </div>
    </Form>
</template>
