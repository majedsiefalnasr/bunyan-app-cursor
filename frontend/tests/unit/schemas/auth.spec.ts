import { describe, expect, it } from 'vitest';

import {
    forgotPasswordSchema,
    loginSchema,
    profileUpdateSchema,
    registerSchema,
    resetPasswordSchema,
} from '~/schemas/auth';

describe('auth schemas', () => {
    it('loginSchema accepts valid credentials', () => {
        const r = loginSchema.safeParse({ email: 'user@example.com', password: 'password1' });
        expect(r.success).toBe(true);
    });

    it('loginSchema rejects short password', () => {
        const r = loginSchema.safeParse({ email: 'user@example.com', password: 'short' });
        expect(r.success).toBe(false);
    });

    it('registerSchema rejects mismatched passwords', () => {
        const r = registerSchema.safeParse({
            name: 'Test User',
            email: 'user@example.com',
            password: 'password1',
            password_confirmation: 'password2',
            phone: '',
        });
        expect(r.success).toBe(false);
    });

    it('registerSchema accepts matching passwords', () => {
        const r = registerSchema.safeParse({
            name: 'Test User',
            email: 'user@example.com',
            password: 'password1',
            password_confirmation: 'password1',
            phone: '',
        });
        expect(r.success).toBe(true);
    });

    it('forgotPasswordSchema validates email', () => {
        expect(forgotPasswordSchema.safeParse({ email: 'bad' }).success).toBe(false);
        expect(forgotPasswordSchema.safeParse({ email: 'ok@example.com' }).success).toBe(true);
    });

    it('resetPasswordSchema enforces confirmation', () => {
        expect(
            resetPasswordSchema.safeParse({
                password: 'password1',
                password_confirmation: 'x',
            }).success
        ).toBe(false);
    });

    it('profileUpdateSchema accepts name and optional phone', () => {
        const r = profileUpdateSchema.safeParse({ name: 'Name', phone: '' });
        expect(r.success).toBe(true);
    });
});
