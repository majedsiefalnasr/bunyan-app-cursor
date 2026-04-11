export type UserRole = 'customer' | 'contractor' | 'architect' | 'engineer' | 'admin';

export interface UserProfile {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    avatar?: string | null;
}
