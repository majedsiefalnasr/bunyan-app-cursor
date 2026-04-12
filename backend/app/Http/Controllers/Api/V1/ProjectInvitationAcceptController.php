<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProjectMemberResource;
use App\Services\ProjectTeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectInvitationAcceptController extends BaseController
{
    public function __construct(private ProjectTeamService $projectTeam)
    {
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return $this->unauthorized();
        }

        $member = $this->projectTeam->acceptInvitation($user, $token);

        return $this->sendSuccess(
            new ProjectMemberResource($member),
            'تم قبول الدعوة بنجاح',
            200,
        );
    }
}
