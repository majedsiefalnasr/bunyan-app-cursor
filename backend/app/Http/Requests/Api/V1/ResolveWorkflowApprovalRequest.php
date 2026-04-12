<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ResolveWorkflowApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approval_id' => ['required', 'integer', 'exists:workflow_approvals,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
