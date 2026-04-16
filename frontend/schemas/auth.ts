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
        password: z
            .string()
            .min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل')
            .refine((v) => /[a-z]/.test(v), 'كلمة المرور يجب أن تحتوي على حرف صغير واحد على الأقل')
            .refine((v) => /[A-Z]/.test(v), 'كلمة المرور لا تطابق المتطلبات')
            .refine((v) => /[0-9]/.test(v), 'كلمة المرور يجب أن تحتوي على رقم واحد على الأقل'),
        password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
        phone: z.string().max(20).optional().or(z.literal('')),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'كلمتا المرور غير متطابقتين',
        path: ['password_confirmation'],
    });

/** Multi-step wizard — step 0 (UI only; API assigns role today) */
export const registerWizardAccountSchema = z.object({
    accountType: z.enum(['customer', 'contractor']),
});

export const registerWizardPersonalSchema = z.object({
    name: z.string().min(1, 'الاسم مطلوب').max(255),
    email: z.string().email('البريد الإلكتروني غير صحيح'),
});

export const registerWizardContactSchema = z.object({
    phone: z.string().max(20).optional().or(z.literal('')),
});

export const registerWizardPasswordSchema = z
    .object({
        password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
        password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'كلمتا المرور غير متطابقتين',
        path: ['password_confirmation'],
    });

/** Wizard step 3 — contact + password (before API submit) */
export const registerWizardCredentialsSchema = z
    .object({
        phone: z.string().max(20).optional().or(z.literal('')),
        password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
        password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
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
export type RegisterWizardAccount = z.infer<typeof registerWizardAccountSchema>;
export type RegisterWizardPersonal = z.infer<typeof registerWizardPersonalSchema>;
export type RegisterWizardContact = z.infer<typeof registerWizardContactSchema>;
export type RegisterWizardPassword = z.infer<typeof registerWizardPasswordSchema>;
export type RegisterWizardCredentials = z.infer<typeof registerWizardCredentialsSchema>;
