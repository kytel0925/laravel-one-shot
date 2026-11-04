<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import SettingsSection from '@/components/SettingsSection.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <SettingsSection
        v-if="canManageTwoFactor"
        title="Two-factor authentication"
        description="Manage your two-factor authentication settings"
    >
        <template v-if="!twoFactorEnabled">
            <p class="text-body-medium text-medium-emphasis">
                When you enable two-factor authentication, you will be prompted
                for a secure pin during login. This pin can be retrieved from a
                TOTP-supported application on your phone.
            </p>

            <div>
                <v-btn
                    v-if="hasSetupData"
                    color="primary"
                    variant="flat"
                    prepend-icon="mdi-shield-check-outline"
                    @click="showSetupModal = true"
                >
                    Continue setup
                </v-btn>
                <Form
                    v-else
                    v-bind="enable.form()"
                    @success="showSetupModal = true"
                    #default="{ processing }"
                >
                    <v-btn
                        type="submit"
                        color="primary"
                        variant="flat"
                        :loading="processing"
                    >
                        Enable 2FA
                    </v-btn>
                </Form>
            </div>
        </template>

        <template v-else>
            <p class="text-body-medium text-medium-emphasis">
                You will be prompted for a secure, random pin during login,
                which you can retrieve from the TOTP-supported application on
                your phone.
            </p>

            <div>
                <Form v-bind="disable.form()" #default="{ processing }">
                    <v-btn
                        type="submit"
                        color="error"
                        variant="flat"
                        :loading="processing"
                    >
                        Disable 2FA
                    </v-btn>
                </Form>
            </div>

            <TwoFactorRecoveryCodes />
        </template>

        <TwoFactorSetupModal
            v-model:isOpen="showSetupModal"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
    </SettingsSection>
</template>
