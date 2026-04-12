# Plan Report — Workflow Engine

> **Generated:** 2026-04-12T16:00:00Z

## Plan summary

Three migrations; four controller entrypoints; two services; instance policy; PHPUnit feature coverage; admin Nuxt list page.

## Guardian verdicts (3.1A)

| Guardian             | Verdict |
| -------------------- | ------- |
| architecture_checker | PASS    |
| api_designer         | PASS    |

## Data touchpoints

See `data-model.md` and `contracts/README.md`.

## Risks

Schema drift on `workflow_configurations.type` — addressed by additive migration.
