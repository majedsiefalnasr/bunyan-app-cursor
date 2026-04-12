# Performance Checklist — Workflow Engine

- [ ] Eager-load `workflowConfiguration`, `workflowable`, `approvalRule` where lists are returned
- [ ] Indexes on `workflow_instance_id`, `workflowable` morph columns
