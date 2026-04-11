export type ErrorCode =
    | 'VALIDATION_ERROR'
    | 'AUTH_INVALID_CREDENTIALS'
    | 'AUTH_TOKEN_EXPIRED'
    | 'AUTH_UNAUTHORIZED'
    | 'RBAC_ROLE_DENIED'
    | 'RESOURCE_NOT_FOUND'
    | 'WORKFLOW_INVALID_TRANSITION'
    | 'WORKFLOW_PREREQUISITES_UNMET'
    | 'PAYMENT_FAILED'
    | 'RATE_LIMIT_EXCEEDED'
    | 'SERVER_ERROR'
    | 'SERVICE_UNAVAILABLE';

export interface ErrorPayload {
    code: ErrorCode | string;
    message: string;
    details?: Record<string, unknown> | null;
    statusCode?: number;
    correlationId?: string;
}

export interface ApiError extends ErrorPayload {
    correlationId?: string;
}

export interface ValidationError extends ApiError {
    fieldErrors: Record<string, string[]>;
}
