import type { ThemeDefinition } from 'vuetify';

/**
 * Supporting text (`text-medium-emphasis`, field labels, card subtitles)
 * renders on-surface at this opacity; 0.72 keeps it above 4.5:1 on every
 * surface role in both themes (MD3 on-surface-variant is ~0.70 equivalent).
 */
const variables = {
    'medium-emphasis-opacity': 0.72,
};

/**
 * Material Design 3 baseline color roles (seed #6750A4), applied over
 * Vuetify's md3 blueprint. Change colors here only; components use the role
 * names (`color="primary-container"`, `bg-surface-container`), never hex.
 *
 * The themes are named `light` and `dark` so Vuetify's `system` theme can
 * follow the operating system preference.
 */
/** Light primary; also the Inertia progress bar color in app.ts. */
export const primaryColor = '#6750A4';

export const lightTheme: ThemeDefinition = {
    dark: false,
    variables,
    colors: {
        primary: primaryColor,
        'on-primary': '#FFFFFF',
        'primary-container': '#EADDFF',
        'on-primary-container': '#21005D',
        secondary: '#625B71',
        'on-secondary': '#FFFFFF',
        'secondary-container': '#E8DEF8',
        'on-secondary-container': '#1D192B',
        tertiary: '#7D5260',
        'on-tertiary': '#FFFFFF',
        'tertiary-container': '#FFD8E4',
        'on-tertiary-container': '#31111D',
        error: '#B3261E',
        'on-error': '#FFFFFF',
        'error-container': '#F9DEDC',
        'on-error-container': '#410E0B',
        success: '#386A20',
        'on-success': '#FFFFFF',
        warning: '#7A5900',
        'on-warning': '#FFFFFF',
        info: '#0061A4',
        'on-info': '#FFFFFF',
        background: '#FEF7FF',
        'on-background': '#1D1B20',
        surface: '#FEF7FF',
        'on-surface': '#1D1B20',
        'surface-bright': '#FEF7FF',
        'surface-light': '#ECE6F0',
        'surface-variant': '#E7E0EC',
        'on-surface-variant': '#49454F',
        'surface-container-lowest': '#FFFFFF',
        'surface-container-low': '#F7F2FA',
        'surface-container': '#F3EDF7',
        'surface-container-high': '#ECE6F0',
        'surface-container-highest': '#E6E0E9',
        'on-surface-container-lowest': '#1D1B20',
        'on-surface-container-low': '#1D1B20',
        'on-surface-container': '#1D1B20',
        'on-surface-container-high': '#1D1B20',
        'on-surface-container-highest': '#1D1B20',
        outline: '#79747E',
        'outline-variant': '#CAC4D0',
        'inverse-surface': '#322F35',
        'inverse-on-surface': '#F5EFF7',
        'inverse-primary': '#D0BCFF',
    },
};

export const darkTheme: ThemeDefinition = {
    dark: true,
    variables,
    colors: {
        primary: '#D0BCFF',
        'on-primary': '#381E72',
        'primary-container': '#4F378B',
        'on-primary-container': '#EADDFF',
        secondary: '#CCC2DC',
        'on-secondary': '#332D41',
        'secondary-container': '#4A4458',
        'on-secondary-container': '#E8DEF8',
        tertiary: '#EFB8C8',
        'on-tertiary': '#492532',
        'tertiary-container': '#633B48',
        'on-tertiary-container': '#FFD8E4',
        error: '#F2B8B5',
        'on-error': '#601410',
        'error-container': '#8C1D18',
        'on-error-container': '#F9DEDC',
        success: '#9CD67D',
        'on-success': '#0C3900',
        warning: '#F5BF48',
        'on-warning': '#402D00',
        info: '#9ECAFF',
        'on-info': '#003258',
        background: '#141218',
        'on-background': '#E6E0E9',
        surface: '#141218',
        'on-surface': '#E6E0E9',
        'surface-bright': '#3B383E',
        'surface-light': '#2B2930',
        'surface-variant': '#49454F',
        'on-surface-variant': '#CAC4D0',
        'surface-container-lowest': '#0F0D13',
        'surface-container-low': '#1D1B20',
        'surface-container': '#211F26',
        'surface-container-high': '#2B2930',
        'surface-container-highest': '#36343B',
        'on-surface-container-lowest': '#E6E0E9',
        'on-surface-container-low': '#E6E0E9',
        'on-surface-container': '#E6E0E9',
        'on-surface-container-high': '#E6E0E9',
        'on-surface-container-highest': '#E6E0E9',
        outline: '#938F99',
        'outline-variant': '#49454F',
        'inverse-surface': '#E6E0E9',
        'inverse-on-surface': '#322F35',
        'inverse-primary': '#6750A4',
    },
};
