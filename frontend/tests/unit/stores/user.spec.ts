import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAuthStore } from '~/stores/auth';
import { useUserStore } from '~/stores/user';
import type { UserProfile } from '~/types/auth';

const profileDefaults: Pick<
    UserProfile,
    'phone' | 'active' | 'email_verified_at' | 'created_at' | 'updated_at'
> = {
    phone: null,
    active: true,
    email_verified_at: null,
    created_at: '2020-01-01T00:00:00.000000Z',
    updated_at: '2020-01-01T00:00:00.000000Z',
};

const mockUser: UserProfile = {
    id: 1,
    name: 'User One',
    email: 'u@example.com',
    role: 'customer',
    ...profileDefaults,
};

describe('useUserStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('fetchProfile delegates to auth store and mirrors profile', async () => {
        const auth = useAuthStore();
        const fetchSpy = vi.spyOn(auth, 'fetchUser').mockResolvedValueOnce(mockUser);
        auth.setToken('jwt');

        const userStore = useUserStore();
        const result = await userStore.fetchProfile();

        expect(fetchSpy).toHaveBeenCalled();
        expect(result).toEqual(mockUser);
        expect(userStore.profile?.email).toBe('u@example.com');
        expect(userStore.loading).toBe(false);
    });

    it('updateProfile delegates to auth store and updates mirrored profile', async () => {
        const auth = useAuthStore();
        auth.setToken('jwt');
        auth.setUser(mockUser);

        const updated: UserProfile = { ...mockUser, name: 'Updated', phone: '+966500000000' };
        const updateSpy = vi.spyOn(auth, 'updateProfile').mockResolvedValueOnce(updated);

        const userStore = useUserStore();
        const result = await userStore.updateProfile({ name: 'Updated', phone: '+966500000000' });

        expect(updateSpy).toHaveBeenCalledWith({ name: 'Updated', phone: '+966500000000' });
        expect(result.name).toBe('Updated');
        expect(userStore.profile?.name).toBe('Updated');
    });

    it('updateProfile records lastError on failure', async () => {
        const auth = useAuthStore();
        auth.setToken('jwt');
        auth.setUser(mockUser);
        vi.spyOn(auth, 'updateProfile').mockRejectedValueOnce({
            data: { error: { message: 'فشل التحديث' } },
        });

        const userStore = useUserStore();
        await expect(userStore.updateProfile({ name: 'X' })).rejects.toBeDefined();
        expect(userStore.lastError).toBe('فشل التحديث');
    });
});
