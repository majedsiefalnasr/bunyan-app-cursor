# Plan Report — Activity Log

> **Generated:** 2026-04-12T12:53:53Z

## Plan Summary

Technical plan covers migration, service/repository layering, admin + subject routes, trait-based logging on `Project`, retention config + prune command, Nuxt admin UI + timeline component, and PHPUnit coverage.

## Research Highlights

See `research.md` — morph indexing, separation from HTTP middleware logging, Nuxt admin conventions.

## Data Model

See `data-model.md` — `activity_logs` columns and indexes.

## Contracts

OpenAPI excerpt: `contracts/activity-log.openapi.yaml`

## Risks

LOW — ensure payload redaction and policy checks on subject route.
