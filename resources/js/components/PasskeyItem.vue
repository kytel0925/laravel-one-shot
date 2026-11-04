<script setup lang="ts">
import { ref } from 'vue';
import type { Passkey } from '@/types/auth';

const props = defineProps<{
    passkey: Passkey;
}>();

const emit = defineEmits<{
    remove: [id: number, onError: () => void];
}>();

const isDeleting = ref(false);
const open = ref(false);

const handleDelete = () => {
    isDeleting.value = true;
    emit('remove', props.passkey.id, () => {
        isDeleting.value = false;
    });
};
</script>

<template>
    <v-list-item prepend-icon="mdi-key-outline">
        <v-list-item-title class="text-title-small">
            {{ passkey.name }}
            <v-chip
                v-if="passkey.authenticator"
                size="small"
                label
                class="ml-2 text-label-small"
            >
                {{ passkey.authenticator }}
            </v-chip>
        </v-list-item-title>
        <v-list-item-subtitle class="text-body-medium">
            Added {{ passkey.created_at_diff }}
            <template v-if="passkey.last_used_at_diff">
                / Last used {{ passkey.last_used_at_diff }}
            </template>
        </v-list-item-subtitle>

        <template #append>
            <v-btn
                icon="mdi-trash-can-outline"
                variant="text"
                color="error"
                aria-label="Remove"
                @click="open = true"
            />
        </template>
    </v-list-item>

    <v-dialog v-model="open" max-width="480">
        <v-card rounded="xl">
            <v-card-title class="text-headline-small pt-6 px-6">
                Remove passkey
            </v-card-title>
            <v-card-text class="text-body-medium px-6">
                Are you sure you want to remove the "{{ passkey.name }}"
                passkey? You will no longer be able to use it to sign in.
            </v-card-text>
            <v-card-actions class="px-6 pb-6">
                <v-spacer />
                <v-btn variant="text" @click="open = false">Cancel</v-btn>
                <v-btn
                    color="error"
                    variant="flat"
                    :loading="isDeleting"
                    @click="handleDelete"
                >
                    {{ isDeleting ? 'Removing...' : 'Remove passkey' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
