# Contract sketch — Inventory API

## GET /api/v1/inventory

**Auth:** Bearer  
**Roles:** admin (all), contractor (supplier-owned products only)  
**200:** `{ success, data: { data: Inventory[], meta: pagination } }`

## PUT /api/v1/inventory/{product}/adjust

**Body:** `{ "quantity_delta": int, "variant_id"?: int, "warehouse_location"?: string, "notes"?: string }`  
**422:** insufficient stock  
**403:** policy

## GET /api/v1/inventory/{product}/movements

Paginated `stock_movements`.

## GET /api/v1/inventory/low-stock

Same shape as index filtered to low threshold.
