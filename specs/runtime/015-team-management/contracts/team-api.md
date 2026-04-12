# Team API Contract (v1)

## `GET /api/v1/projects/{project}/team`

**Auth:** Sanctum  
**Policy:** `view` project  
**200 data:** `{ "members": [...], "invitations_pending": [...] }`

## `POST /api/v1/projects/{project}/team`

**Auth:** Sanctum + roles: customer, contractor, supervising_architect, admin  
**Policy:** `manageTeam`  
**Body:** `{ "user_id": number }` XOR `{ "email": string }`, plus `project_role` (not `owner` for invitations — service enforces)  
**201:** `ProjectMemberResource` or `ProjectInvitationResource` + optional `accept_token` on invitation

## `PUT /api/v1/projects/{project}/team/{user}`

**Body:** `{ "project_role": "manager" | ... }`  
**Policy:** `manageTeam`

## `DELETE /api/v1/projects/{project}/team/{user}`

**Policy:** `manageTeam` + owner safeguards

## `POST /api/v1/invitations/{token}/accept`

**Auth:** Sanctum  
**204/200:** member created, invitation `accepted_at` set

Errors follow Bunyan standard `{ success, message, errors }`.
