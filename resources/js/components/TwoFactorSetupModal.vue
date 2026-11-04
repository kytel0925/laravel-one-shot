<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { confirm } from '@/routes/two-factor';
import type { TwoFactorConfigContent } from '@/types';

type Props = {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
};

const props = defineProps<Props>();
const isOpen = defineModel<boolean>('isOpen');

const copied = ref(false);

const copy = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        copied.value = false;
    }
};

const { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } =
    useTwoFactorAuth();

const showVerificationStep = ref(false);
const code = ref<string>('');

const pinInputContainerRef = useTemplateRef<HTMLElement>(
    'pinInputContainerRef',
);

const modalConfig = computed<TwoFactorConfigContent>(() => {
    if (props.twoFactorEnabled) {
        return {
            title: 'Two-factor authentication enabled',
            description:
                'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.',
            buttonText: 'Close',
        };
    }

    if (showVerificationStep.value) {
        return {
            title: 'Verify authentication code',
            description: 'Enter the 6-digit code from your authenticator app',
            buttonText: 'Continue',
        };
    }

    return {
        title: 'Enable two-factor authentication',
        description:
            'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app',
        buttonText: 'Continue',
    };
});

const handleModalNextStep = () => {
    if (props.requiresConfirmation) {
        showVerificationStep.value = true;

        nextTick(() => {
            pinInputContainerRef.value?.querySelector('input')?.focus();
        });

        return;
    }

    clearSetupData();
    isOpen.value = false;
};

const resetModalState = () => {
    if (props.twoFactorEnabled) {
        clearSetupData();
    }

    showVerificationStep.value = false;
    code.value = '';
};

watch(
    () => isOpen.value,
    async (isOpen) => {
        if (!isOpen) {
            resetModalState();

            return;
        }

        if (!qrCodeSvg.value) {
            await fetchSetupData();
        }
    },
);
</script>

<template>
    <v-dialog
        :model-value="isOpen"
        max-width="480"
        @update:model-value="isOpen = $event"
    >
        <v-card rounded="xl">
            <v-card-item class="pt-6 px-6">
                <template #prepend>
                    <v-avatar color="primary-container" size="48">
                        <v-icon icon="mdi-qrcode-scan" />
                    </v-avatar>
                </template>
                <v-card-title class="text-headline-small text-wrap">
                    {{ modalConfig.title }}
                </v-card-title>
            </v-card-item>
            <v-card-text class="px-6">
                <p class="text-body-medium text-medium-emphasis mb-4">
                    {{ modalConfig.description }}
                </p>

                <template v-if="!showVerificationStep">
                    <v-alert v-if="errors?.length" type="error">
                        <div v-for="(error, index) in errors" :key="index">
                            {{ error }}
                        </div>
                    </v-alert>
                    <div v-else class="d-flex flex-column ga-4">
                        <v-sheet
                            color="white"
                            rounded="lg"
                            class="pa-4 mx-auto d-flex align-center justify-center"
                            width="256"
                            height="256"
                        >
                            <v-progress-circular
                                v-if="!qrCodeSvg"
                                indeterminate
                                color="primary"
                            />
                            <div v-else v-html="qrCodeSvg" class="qr-code" />
                        </v-sheet>

                        <v-btn
                            color="primary"
                            variant="flat"
                            block
                            @click="handleModalNextStep"
                        >
                            {{ modalConfig.buttonText }}
                        </v-btn>

                        <div class="d-flex align-center ga-3">
                            <v-divider />
                            <span
                                class="text-label-large text-medium-emphasis text-no-wrap"
                            >
                                or, enter the code manually
                            </span>
                            <v-divider />
                        </div>

                        <v-text-field
                            v-if="manualSetupKey"
                            :model-value="manualSetupKey"
                            readonly
                            hide-details
                            label="Setup key"
                            class="text-mono"
                            :append-inner-icon="
                                copied ? 'mdi-check' : 'mdi-content-copy'
                            "
                            @click:append-inner="copy(manualSetupKey || '')"
                        />
                        <v-progress-linear v-else indeterminate />
                    </div>
                </template>

                <Form
                    v-else
                    v-bind="confirm.form()"
                    error-bag="confirmTwoFactorAuthentication"
                    reset-on-error
                    @finish="code = ''"
                    @success="isOpen = false"
                    v-slot="{ errors, processing }"
                >
                    <input type="hidden" name="code" :value="code" />
                    <div
                        ref="pinInputContainerRef"
                        class="d-flex flex-column ga-4"
                    >
                        <v-otp-input
                            v-model="code"
                            length="6"
                            autofocus
                            :disabled="processing"
                            :error="!!errors?.code"
                        />
                        <p
                            v-if="errors?.code"
                            class="text-body-medium text-error text-center"
                        >
                            {{ errors.code }}
                        </p>

                        <div class="d-flex justify-end ga-2">
                            <v-btn
                                type="button"
                                variant="text"
                                :disabled="processing"
                                @click="showVerificationStep = false"
                            >
                                Back
                            </v-btn>
                            <v-btn
                                type="submit"
                                color="primary"
                                variant="flat"
                                :loading="processing"
                                :disabled="code.length < 6"
                            >
                                Confirm
                            </v-btn>
                        </div>
                    </div>
                </Form>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<style scoped>
.qr-code :deep(svg) {
    display: block;
    width: 100%;
    height: 100%;
}
</style>
