import type { UserRole } from '~/types/auth';

export interface NavItem {
    labelKey: string;
    to: string;
    icon: string;
    roles: UserRole[];
    permissions?: string[];
    badge?: number;
}

export const navigationItems: NavItem[] = [
    {
        labelKey: 'nav.home',
        to: '/',
        icon: 'i-heroicons-home',
        roles: [],
    },
    {
        labelKey: 'nav.dashboard',
        to: '/dashboard',
        icon: 'i-heroicons-squares-2x2',
        roles: ['customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin'],
    },
    {
        labelKey: 'nav.projects',
        to: '/projects',
        icon: 'i-heroicons-building-office',
        roles: ['customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin'],
    },
    {
        labelKey: 'nav.messages',
        to: '/messages',
        icon: 'i-heroicons-chat-bubble-left-right',
        roles: ['customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin'],
    },
    {
        labelKey: 'nav.media',
        to: '/media',
        icon: 'i-heroicons-photo',
        roles: ['customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin'],
    },
    {
        labelKey: 'nav.reports',
        to: '/reports',
        icon: 'i-heroicons-document-text',
        roles: ['contractor', 'field_engineer', 'admin'],
    },
    {
        labelKey: 'nav.products',
        to: '/products',
        icon: 'i-heroicons-shopping-bag',
        roles: ['customer', 'admin'],
    },
    {
        labelKey: 'nav.admin',
        to: '/admin',
        icon: 'i-heroicons-cog-6-tooth',
        roles: ['admin'],
    },
];
