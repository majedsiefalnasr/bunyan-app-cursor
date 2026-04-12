# Performance Checklist — Workflow Engine

- [x] Eager-load `workflowConfiguration`, `workflowable`, `approvalRule` where lists are returned
- [x] Indexes on `workflow_instance_id`, `workflowable` morph columns
