import type { InjectionKey, Ref } from 'vue';

export interface ProjectShellDetail {
    id: number;
    name: string;
    status: string;
    start_date: string | null;
    end_date: string | null;
}

export const PROJECT_SHELL_KEY: InjectionKey<Ref<ProjectShellDetail | null>> =
    Symbol('projectShellDetail');
