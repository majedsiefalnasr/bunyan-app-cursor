# Data Model — Analytics Reports (Read Path)

| Source   | Columns used (indicative)                     | Report types                                        |
| -------- | --------------------------------------------- | --------------------------------------------------- |
| orders   | total_amount, status, created_at, supplier_id | sales_summary, orders_summary, supplier_performance |
| products | stock_quantity, sku, name                     | inventory_low_stock                                 |
| projects | status, progress fields if any                | project_status                                      |
| invoices | totals, tax                                   | financial_summary (partial/stub)                    |

Relationships resolved in repository with `select()` to limit columns.
