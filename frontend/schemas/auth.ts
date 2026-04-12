import { z } from 'zod';

/** Login — matches `LoginPayload` and `/api/v1/auth/login` */
export const loginSchema = z.object({
    email: z.string().email('البريد الإلكتروني غير صحيح'),
    password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
});

/** Registration — matches `RegisterPayload` */
export const registerSchema = z
    .object({
        name: z.string().min(1, 'الاسم مطلوب').max(255),
        email: z.string().email('البريد الإلكتروني غير صحيح'),
        password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
        password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
        phone: z.string().max(20).optional().or(z.literal('')),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'كلمتا المرور غير متطابقتين',
        path: ['password_confirmation'],
    });

export const forgotPasswordSchema = z.object({
    email: z.string().email('البريد الإلكتروني غير صحيح'),
});

export const resetPasswordSchema = z
    .object({
        password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
        password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'كلمتا المرور غير متطابقتين',
        path: ['password_confirmation'],
    });

/** Profile update — matches `UpdateProfileRequest` (name, phone) */
export const profileUpdateSchema = z.object({
    name: z.string().min(1, 'الاسم مطلوب').max(255),
    phone: z.string().max(20, 'رقم الهاتف طويل جدًا').optional().or(z.literal('')),
});

export type LoginFormValues = z.infer<typeof loginSchema>;
export type RegisterFormValues = z.infer<typeof registerSchema>;
export type ForgotPasswordFormValues = z.infer<typeof forgotPasswordSchema>;
export type ResetPasswordFormValues = z.infer<typeof resetPasswordSchema>;
export type ProfileUpdateFormValues = z.infer<typeof profileUpdateSchema>;
