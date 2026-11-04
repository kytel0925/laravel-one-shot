import { describe, expect, it } from 'vite-plus/test';
import { darkTheme, lightTheme } from './theme';

describe('MD3 themes', () => {
    it('define the same color roles in light and dark', () => {
        expect(Object.keys(darkTheme.colors ?? {}).sort()).toEqual(
            Object.keys(lightTheme.colors ?? {}).sort(),
        );
    });

    it('pair every color role with its on- role', () => {
        const roles = Object.keys(lightTheme.colors ?? {});
        const missing = roles
            .filter(
                (role) =>
                    !role.startsWith('on-') &&
                    ![
                        'surface-bright',
                        'surface-light',
                        'surface-variant',
                    ].includes(role) &&
                    !role.startsWith('inverse-') &&
                    !role.startsWith('outline'),
            )
            .filter((role) => !roles.includes(`on-${role}`));

        expect(missing).toEqual([]);
    });

    it('use the right dark flag', () => {
        expect(lightTheme.dark).toBe(false);
        expect(darkTheme.dark).toBe(true);
    });
});
