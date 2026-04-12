export type UserRole =
    | 'customer'
    | 'contractor'
    | 'supervising_architect'
    | 'field_engineer'
    | 'admin';

export interface UserProfile {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    /** RBAC permission names returned by the API (login, profile, etc.) */
    permissions?: string[];
    /** Optional avatar URL when the API returns one */
    avatar?: string | null;
    phone: string | null;
    active: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface LoginPayload {
    email: string;
    password: string;
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    phone?: string;
}

export interface AuthResponse {
    user: UserProfile;
    token: string;
}

export interface ApiSuccessResponse<T = unknown> {
    success: true;
    data: T;
    message: string | null;
    errors: Record<string, string[]>;
    error: null;
}

export interface ApiErrorResponse {
    success: false;
    data: null;
    message: string | null;
    errors: Record<string, string[]>;
    error: {
        code: string;
        message: string;
        details: Record<string, unknown> | null;
    };
}
