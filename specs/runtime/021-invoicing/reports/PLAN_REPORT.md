# Plan Report — Invoicing

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T12:20:00Z

## Plan Summary

| Metric         | Value |
| -------------- | ----- |
| New Tables     | 2     |
| New Endpoints  | 6     |
| New Services   | 2     |
| New Pages      | 3     |
| New Components | 0     |

## Architecture Decisions

- Dompdf for PDF; ZATCA TLV builder as dedicated small class (no external ZATCA SDK).
- Invoice lines store denormalized monetary fields for stable PDF/reprints.
- Auto-invoice inside `OrderService` transaction with idempotent check via repository.

## Guardian Verdicts

| Guardian              | Verdict | Notes                          |
| --------------------- | ------- | ------------------------------ |
| Architecture Guardian | PASS    | Layering and RBAC preserved    |
| API Designer          | PASS    | REST plural resource, envelope |

## Risk Assessment

| Risk Level | Count | Details                     |
| ---------- | ----- | --------------------------- |
| HIGH       | 0     |                             |
| MEDIUM     | 1     | Financial rounding / VAT    |
| LOW        | 2     | Mail driver config, PDF CSS |
