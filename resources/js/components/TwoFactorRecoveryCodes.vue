<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';
import { VExpandTransition } from 'vuetify/components';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { regenerateRecoveryCodes } from '@/routes/two-factor';

const { recoveryCodesList, fetchRecoveryCodes, errors } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = useTemplateRef('recoveryCodeSectionRef');

const toggleRecoveryCodesVisibility = async () => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        (
            recoveryCodeSectionRef.value?.$el as HTMLElement | undefined
        )?.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
});
</script>

<template>
    <v-card variant="outlined" rounded="xl">
        <v-card-item prepend-icon="mdi-lock-outline">
            <v-card-title class="text-title-medium">
                2FA recovery codes
            </v-card-title>
            <v-card-subtitle class="text-body-medium text-wrap">
                Recovery codes let you regain access if you lose your 2FA
                device. Store them in a secure password manager.
            </v-card-subtitle>
        </v-card-item>
        <v-card-text>
            <div class="d-flex flex-wrap align-center ga-3">
                <v-btn
                    color="primary"
                    variant="flat"
                    :prepend-icon="
                        isRecoveryCodesVisible
                            ? 'mdi-eye-off-outline'
                            : 'mdi-eye-outline'
                    "
                    @click="toggleRecoveryCodesVisibility"
                >
                    {{ isRecoveryCodesVisible ? 'Hide' : 'View' }} recovery
                    codes
                </v-btn>

                <Form
                    v-if="isRecoveryCodesVisible && recoveryCodesList.length"
                    v-bind="regenerateRecoveryCodes.form()"
                    method="post"
                    :options="{ preserveScroll: true }"
                    @success="fetchRecoveryCodes"
                    #default="{ processing }"
                >
                    <v-btn
                        variant="tonal"
                        type="submit"
                        prepend-icon="mdi-refresh"
                        :loading="processing"
                    >
                        Regenerate codes
                    </v-btn>
                </Form>
            </div>

            <VExpandTransition>
                <div v-show="isRecoveryCodesVisible">
                    <v-alert v-if="errors?.length" type="error" class="mt-4">
                        <div v-for="(error, index) in errors" :key="index">
                            {{ error }}
                        </div>
                    </v-alert>
                    <div v-else class="mt-4 d-flex flex-column ga-3">
                        <v-sheet
                            ref="recoveryCodeSectionRef"
                            color="surface-container-high"
                            rounded="lg"
                            class="pa-4 text-mono text-body-medium"
                        >
                            <v-skeleton-loader
                                v-if="!recoveryCodesList.length"
                                type="list-item@4"
                            />
                            <div
                                v-else
                                v-for="(code, index) in recoveryCodesList"
                                :key="index"
                            >
                                {{ code }}
                            </div>
                        </v-sheet>
                        <p
                            class="text-label-small text-medium-emphasis text-wrap"
                        >
                            Each recovery code can be used once to access your
                            account and will be removed after use. If you need
                            more, click
                            <strong>Regenerate codes</strong> above.
                        </p>
                    </div>
                </div>
            </VExpandTransition>
        </v-card-text>
    </v-card>
</template>
