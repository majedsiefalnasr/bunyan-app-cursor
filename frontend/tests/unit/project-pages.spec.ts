import { describe, expect, it } from 'vitest';
import { projectStatusBadgeColor } from '~/composables/useProjectStatus';
import {
    normalizeProjectTasksPayload,
    tasksForColumn,
    type TaskBoardRow,
} from '~/composables/useTaskBoard';

describe('useProjectStatus', () => {
    it('maps planning-like statuses to blue', () => {
        expect(projectStatusBadgeColor('PLANNING')).toBe('blue');
    });

    it('maps in-progress statuses to green', () => {
        expect(projectStatusBadgeColor('IN_PROGRESS')).toBe('green');
    });

    it('maps hold statuses to orange', () => {
        expect(projectStatusBadgeColor('ON_HOLD')).toBe('orange');
    });
});

describe('useTaskBoard', () => {
    it('normalizes array payloads', () => {
        const rows: TaskBoardRow[] = [
            {
                id: 1,
                title_ar: 'مهمة',
                title_en: null,
                name: 't',
                status: 'todo',
                priority: 'normal',
                phase_id: 1,
            },
        ];
        expect(normalizeProjectTasksPayload(rows)).toEqual(rows);
    });

    it('normalizes paginated payloads', () => {
        const rows: TaskBoardRow[] = [
            {
                id: 2,
                title_ar: null,
                title_en: null,
                name: 'x',
                status: 'done',
                priority: 'normal',
                phase_id: 1,
            },
        ];
        expect(normalizeProjectTasksPayload({ data: rows })).toEqual(rows);
    });

    it('groups tasks by column', () => {
        const rows: TaskBoardRow[] = [
            {
                id: 1,
                title_ar: null,
                title_en: null,
                name: 'a',
                status: 'todo',
                priority: 'normal',
                phase_id: 1,
            },
            {
                id: 2,
                title_ar: null,
                title_en: null,
                name: 'b',
                status: 'todo',
                priority: 'normal',
                phase_id: 1,
            },
        ];
        expect(tasksForColumn(rows, 'todo')).toHaveLength(2);
        expect(tasksForColumn(rows, 'done')).toHaveLength(0);
    });
});
