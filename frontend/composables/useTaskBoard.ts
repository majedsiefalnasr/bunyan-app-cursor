export const TASK_BOARD_COLUMNS = ['todo', 'in_progress', 'in_review', 'done', 'blocked'] as const;

export type TaskBoardColumn = (typeof TASK_BOARD_COLUMNS)[number];

export interface TaskBoardRow {
    id: number;
    title_ar: string | null;
    title_en: string | null;
    name: string;
    status: string;
    priority: string;
    phase_id: number;
}

export function normalizeProjectTasksPayload(payload: unknown): TaskBoardRow[] {
    if (Array.isArray(payload)) {
        return payload as TaskBoardRow[];
    }
    if (payload && typeof payload === 'object') {
        const p = payload as Record<string, unknown>;
        if (Array.isArray(p.data)) {
            return p.data as TaskBoardRow[];
        }
        if (
            p.data &&
            typeof p.data === 'object' &&
            Array.isArray((p.data as { data?: TaskBoardRow[] }).data)
        ) {
            return (p.data as { data: TaskBoardRow[] }).data;
        }
    }
    return [];
}

export function tasksForColumn(tasks: TaskBoardRow[], column: TaskBoardColumn): TaskBoardRow[] {
    return tasks.filter((t) => t.status === column);
}
