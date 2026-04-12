<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProjectRole;
use App\Http\Requests\Api\V1\StoreProjectTeamMemberRequest;
use App\Http\Requests\Api\V1\UpdateProjectTeamMemberRequest;
use App\Http\Resources\Api\V1\ProjectInvitationResource;
use App\Http\Resources\Api\V1\ProjectMemberResource;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectTeamService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ProjectTeamController extends BaseController
{
    public function __construct(private ProjectTeamService $projectTeam)
    {
    }

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $data = $this->projectTeam->listTeam($project);

        return $this->sendSuccess([
            'members' => ProjectMemberResource::collection($data['members']),
            'invitations_pending' => ProjectInvitationResource::collection($data['invitations_pending']),
        ], 'تم جلب فريق المشروع بنجاح', 200);
    }

    public function store(StoreProjectTeamMemberRequest $request, Project $project): JsonResponse
    {
        $result = $this->projectTeam->addOrInvite($project, $request->user(), $request->validated());

        if ($result['type'] === 'member') {
            return $this->sendSuccess(
                new ProjectMemberResource($result['member']),
                'تمت إضافة عضو الفريق بنجاح',
                201,
            );
        }

        return $this->sendSuccess(
            [
                'invitation' => new ProjectInvitationResource($result['invitation']),
                'accept_token' => $result['plain_token'],
            ],
            'تم إنشاء الدعوة بنجاح',
            201,
        );
    }

    public function update(UpdateProjectTeamMemberRequest $request, Project $project, User $user): JsonResponse
    {
        try {
            $role = ProjectRole::from($request->validated('project_role'));
            $member = $this->projectTeam->updateMemberRole($project, $user, $role);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        return $this->sendSuccess(
            new ProjectMemberResource($member),
            'تم تحديث دور العضو بنجاح',
            200,
        );
    }

    public function destroy(Project $project, User $user): JsonResponse
    {
        $this->authorize('manageTeam', $project);

        try {
            $this->projectTeam->removeMember($project, $user);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        return $this->sendSuccess(null, 'تمت إزالة العضو من الفريق بنجاح', 200);
    }
}
