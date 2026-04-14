<?php

namespace Tests\Unit\Repositories\Analytics;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use App\Models\SupplierProfile;
use App\Repositories\Analytics\BusinessAnalyticsReportRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAnalyticsReportRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_summary_filters_by_date_and_calculates_average_order_value(): void
    {
        $repo = new BusinessAnalyticsReportRepository;

        Order::factory()->create([
            'total_amount' => 100,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        Order::factory()->create([
            'total_amount' => 200,
            'created_at' => '2026-01-02 10:00:00',
        ]);
        Order::factory()->create([
            'total_amount' => 999,
            'created_at' => '2026-02-01 10:00:00',
        ]);

        $result = $repo->salesSummary([
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(2, $result['summary']['order_count']);
        $this->assertSame('300', $result['summary']['revenue']);
        $this->assertSame('150.00', $result['summary']['average_order_value']);
        $this->assertFalse($result['truncated']);
        $this->assertCount(2, $result['rows']);
    }

    public function test_orders_summary_groups_by_status(): void
    {
        $repo = new BusinessAnalyticsReportRepository;

        Order::factory()->create(['status' => 'pending', 'created_at' => '2026-01-01 10:00:00']);
        Order::factory()->create(['status' => 'pending', 'created_at' => '2026-01-01 12:00:00']);
        Order::factory()->create(['status' => 'confirmed', 'created_at' => '2026-01-02 10:00:00']);

        $result = $repo->ordersSummary([
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(3, $result['summary']['order_count']);
        $this->assertSame(2, $result['summary']['by_status']['pending']);
        $this->assertSame(1, $result['summary']['by_status']['confirmed']);
        $this->assertCount(3, $result['rows']);
    }

    public function test_project_status_summarizes_by_status(): void
    {
        $repo = new BusinessAnalyticsReportRepository;

        Project::factory()->create(['status' => 'in_progress', 'created_at' => '2026-01-01 10:00:00']);
        Project::factory()->create(['status' => 'completed', 'created_at' => '2026-01-02 10:00:00']);
        Project::factory()->create(['status' => 'completed', 'created_at' => '2026-01-03 10:00:00']);

        $result = $repo->projectStatus([
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(3, $result['summary']['project_count']);
        $this->assertSame(1, $result['summary']['by_status']['in_progress']);
        $this->assertSame(2, $result['summary']['by_status']['completed']);
        $this->assertCount(3, $result['rows']);
    }

    public function test_inventory_low_stock_filters_by_category_and_supplier(): void
    {
        $repo = new BusinessAnalyticsReportRepository;

        $category = Category::factory()->create();
        $supplier = SupplierProfile::factory()->verified()->create();

        Product::factory()->create([
            'active' => true,
            'quantity_in_stock' => 10,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        Product::factory()->create([
            'active' => true,
            'quantity_in_stock' => 11,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        Product::factory()->create([
            'active' => false,
            'quantity_in_stock' => 2,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $repo->inventoryLowStock([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $this->assertSame(1, $result['summary']['low_stock_count']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_supplier_performance_sums_revenue_and_counts_orders(): void
    {
        $repo = new BusinessAnalyticsReportRepository;

        $supplier = SupplierProfile::factory()->verified()->create([
            'company_name_ar' => 'شركة',
            'company_name_en' => null,
            'rating_avg' => 4.2,
        ]);

        Order::factory()->create([
            'supplier_id' => $supplier->id,
            'total_amount' => 100,
            'created_at' => '2026-01-01 10:00:00',
        ]);
        Order::factory()->create([
            'supplier_id' => $supplier->id,
            'total_amount' => 250,
            'created_at' => '2026-01-02 10:00:00',
        ]);

        $result = $repo->supplierPerformance([
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(1, $result['summary']['supplier_count']);
        $this->assertSame('350', $result['summary']['total_revenue']);
        $this->assertCount(1, $result['rows']);
        $this->assertSame($supplier->id, $result['rows'][0]['supplier_profile_id']);
        $this->assertSame('شركة', $result['rows'][0]['supplier_name']);
        $this->assertSame(2, $result['rows'][0]['order_count']);
        $this->assertSame('350', $result['rows'][0]['revenue']);
    }

    public function test_financial_summary_uses_invoices_table(): void
    {
        $repo = new BusinessAnalyticsReportRepository;

        $supplier = SupplierProfile::factory()->verified()->create();
        $customerId = $supplier->user_id;

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-1',
            'order_id' => null,
            'customer_id' => $customerId,
            'supplier_id' => $supplier->id,
            'subtotal' => 100,
            'vat_amount' => 15,
            'vat_percentage' => 15,
            'total' => 115,
            'status' => 'draft',
            'due_date' => now()->addDays(7)->toDateString(),
            'paid_at' => null,
            'zatca_qr_data' => null,
            'notes' => null,
        ]);
        $invoice->created_at = '2026-01-01 10:00:00';
        $invoice->updated_at = '2026-01-01 10:00:00';
        $invoice->saveQuietly();

        $result = $repo->financialSummary([
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(1, $result['summary']['invoice_count']);
        $this->assertSame('115', $result['summary']['invoiced_total']);
        $this->assertSame('15', $result['summary']['vat_total']);
        $this->assertTrue($result['summary']['stub']);
        $this->assertCount(1, $result['rows']);
    }
}
