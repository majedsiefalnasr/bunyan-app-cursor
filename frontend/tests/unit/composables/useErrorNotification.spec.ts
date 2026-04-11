import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useErrorNotification } from '~/composables/useErrorNotification';

import { toastAdd } from '../../setup';

describe('useErrorNotification', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('shows toast with error severity for 5xx', () => {
        const { showErrorNotification } = useErrorNotification();

        showErrorNotification({ code: 'SERVER_ERROR', message: 'x', statusCode: 500 });

        expect(toastAdd).toHaveBeenCalledWith(
            expect.objectContaining({
                color: 'red',
                timeout: 8000,
            })
        );
    });

    it('shows toast with warning severity for 4xx', () => {
        const { showErrorNotification } = useErrorNotification();

        showErrorNotification({ code: 'VALIDATION_ERROR', message: 'x', statusCode: 422 });

        expect(toastAdd).toHaveBeenCalledWith(
            expect.objectContaining({
                color: 'yellow',
                timeout: 5000,
            })
        );
    });
});
