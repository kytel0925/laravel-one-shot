<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { ref } from 'vue';

const emit = defineEmits<{
    success: [];
}>();

const getDefaultPasskeyName = () => {
    const ua = navigator.userAgent;

    const browser = [
        { pattern: /Edg|Edge/, name: 'Edge' },
        { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
        { pattern: /Firefox|FxiOS/, name: 'Firefox' },
        { pattern: /Chrome|CriOS/, name: 'Chrome' },
        { pattern: /Safari/, name: 'Safari' },
    ].find(({ pattern }) => pattern.test(ua))?.name;

    const os = [
        { pattern: /iPhone/, name: 'iPhone' },
        { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
        { pattern: /Android/, name: 'Android' },
        { pattern: /Mac/, name: 'Mac' },
        { pattern: /Windows/, name: 'Windows' },
    ].find(({ pattern }) => pattern.test(ua))?.name;

    return [browser, os].filter(Boolean).join(' on ') || '';
};

const name = ref(getDefaultPasskeyName());
const showForm = ref(false);

const { register, isLoading, error, isSupported } = usePasskeyRegister({
    onSuccess: () => {
        name.value = '';
        showForm.value = false;
        emit('success');
    },
});

const handleSubmit = async (event: Event) => {
    event.preventDefault();

    if (!name.value.trim()) {
        return;
    }

    await register(name.value);
};

const handleCancel = () => {
    showForm.value = false;
    name.value = '';
};
</script>

<template>
    <p v-if="!isSupported" class="text-body-medium text-medium-emphasis">
        Passkeys are not supported in this browser.
    </p>

    <div v-else-if="!showForm">
        <v-btn variant="tonal" @click="showForm = true">Add passkey</v-btn>
    </div>

    <v-sheet v-else color="surface-container-high" rounded="lg" class="pa-4">
        <form class="d-flex flex-column ga-2" @submit="handleSubmit">
            <v-text-field
                id="passkey-name"
                v-model="name"
                v-focus
                label="Passkey name"
                placeholder="e.g., MacBook Pro, iPhone"
                hint="A name helps you identify this passkey later."
                persistent-hint
                :error-messages="error ? [error] : []"
            />

            <div class="d-flex ga-2">
                <v-btn
                    type="submit"
                    color="primary"
                    variant="flat"
                    :loading="isLoading"
                    :disabled="!name.trim()"
                >
                    {{ isLoading ? 'Registering...' : 'Register passkey' }}
                </v-btn>
                <v-btn type="button" variant="text" @click="handleCancel">
                    Cancel
                </v-btn>
            </div>
        </form>
    </v-sheet>
</template>
