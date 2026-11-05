<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { Passkey } from '@/types/auth';
import PasskeyItem from '@/components/PasskeyItem.vue';
import PasskeyRegister from '@/components/PasskeyRegister.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

withDefaults(defineProps<Props>(), {
    canManagePasskeys: false,
    passkeys: () => [],
});

const handleDelete = (id: number, onError: () => void) => {
    router.delete(destroy.url(id), {
        preserveScroll: true,
        onError,
    });
};

const handleRegisterSuccess = () => {
    router.reload();
};
</script>

<template>
    <SettingsSection
        v-if="canManagePasskeys"
        title="Passkeys"
        description="Manage your passkeys for passwordless sign-in"
    >
        <v-card v-if="passkeys.length" variant="outlined" rounded="lg">
            <template v-for="(passkey, index) in passkeys" :key="passkey.id">
                <v-divider v-if="index > 0" />
                <PasskeyItem :passkey="passkey" @remove="handleDelete" />
            </template>
        </v-card>

        <v-empty-state
            v-else
            icon="mdi-key-outline"
            title="No passkeys yet"
            text="Add a passkey to sign in without a password"
        />

        <PasskeyRegister @success="handleRegisterSuccess" />
    </SettingsSection>
</template>
