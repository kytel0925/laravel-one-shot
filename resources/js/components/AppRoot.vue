<script setup lang="ts">
import { onMounted } from 'vue';
import { useTheme } from 'vuetify';
import { useAppearance } from '@/composables/useAppearance';
import { snackbarQueue } from '@/lib/flashToast';

// Layout props meant for the inner layouts must not land on <v-app>.
defineOptions({ inheritAttrs: false });

const theme = useTheme();
const { appearance } = useAppearance();

// Hydration used the server's theme; now follow the real appearance
// (`system` resolves to the current OS color scheme).
onMounted(() => {
    if (theme.name.value !== appearance.value) {
        void theme.change(appearance.value);
    }
});
</script>

<template>
    <v-app>
        <slot />

        <v-snackbar-queue
            v-model="snackbarQueue"
            location="bottom center"
            closable
            data-test="snackbar-queue"
        />
    </v-app>
</template>
