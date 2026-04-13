<?php

namespace App\Services;

use App\Enums\EstimateItemCategory;
use App\Enums\EstimateStatus;
use App\Enums\UserRole;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Project;
use App\Models\User;
use App\Repositories\EstimateItemRepository;
use App\Repositories\EstimateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstimateService
{
    public function __construct(
        private EstimateRepository $estimateRepository,
        private EstimateItemRepository $itemRepository,
    ) {
    }

    public function createEstimate(User $user, Project $project, string $title, ?string $description, float $markup): Estimate
    {
        return $this->estimateRepository->create([
            'project_id' => $project->id,
            'title' => $title,
            'description' => $description,
            'status' => EstimateStatus::Draft,
            'total_materials' => 0,
            'total_labor' => 0,
            'total_overhead' => 0,
            'grand_total' => 0,
            'markup_percentage' => $markup,
            'created_by' => $user->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateEstimate(User $user, Estimate $estimate, array $data): Estimate
    {
        if (in_array($estimate->status, [EstimateStatus::Approved, EstimateStatus::Rejected], true)) {
            if ($user->role !== UserRole::Admin) {
                throw ValidationException::withMessages([
                    'status' => ['لا يمكن تعديل تقدير معتمد أو مرفوض'],
                ]);
            }
        }

        $payload = [];
        if (array_key_exists('title', $data)) {
            $payload['title'] = $data['title'];
        }
        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }
        if (array_key_exists('markup_percentage', $data)) {
            $payload['markup_percentage'] = $data['markup_percentage'];
        }
        if (isset($data['status'])) {
            $newStatus = EstimateStatus::from((string) $data['status']);
            $this->assertStatusTransition($estimate, $newStatus, $user);
            $payload['status'] = $newStatus;
        }

        return $this->estimateRepository->update($estimate, $payload);
    }

    private function assertStatusTransition(Estimate $estimate, EstimateStatus $new, User $user): void
    {
        $current = $estimate->status;
        if ($new === $current) {
            return;
        }
        if ($new === EstimateStatus::Submitted) {
            if ($current !== EstimateStatus::Draft) {
                throw ValidationException::withMessages(['status' => ['انتقال حالة غير صالح']]);
            }
            if ($this->itemRepository->countForEstimate($estimate) < 1) {
                throw ValidationException::withMessages(['status' => ['أضف بندًا واحدًا على الأقل قبل الإرسال']]);
            }
            if (! $this->canMutateEstimates($user)) {
                throw ValidationException::withMessages(['status' => ['غير مصرح']]);
            }

            return;
        }
        throw ValidationException::withMessages(['status' => ['حالة غير مسموحة في هذا المسار']]);
    }

    public function canMutateEstimates(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Customer,
            UserRole::Contractor,
            UserRole::SupervisingArchitect,
            UserRole::Admin,
        ], true);
    }

    public function approve(User $user, Estimate $estimate): Estimate
    {
        if (! $this->canApprove($user)) {
            throw ValidationException::withMessages(['estimate' => ['غير مصرح بالموافقة']]);
        }
        if ($estimate->status !== EstimateStatus::Submitted) {
            throw ValidationException::withMessages(['estimate' => ['يجب أن يكون التقدير في حالة مرسل']]);
        }

        return $this->estimateRepository->update($estimate, [
            'status' => EstimateStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $user, Estimate $estimate): Estimate
    {
        if (! $this->canApprove($user)) {
            throw ValidationException::withMessages(['estimate' => ['غير مصرح بالرفض']]);
        }
        if ($estimate->status !== EstimateStatus::Submitted) {
            throw ValidationException::withMessages(['estimate' => ['يجب أن يكون التقدير في حالة مرسل']]);
        }

        return $this->estimateRepository->update($estimate, [
            'status' => EstimateStatus::Rejected,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    private function canApprove(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Customer,
            UserRole::SupervisingArchitect,
            UserRole::Admin,
        ], true);
    }

    public function recalculate(Estimate $estimate): Estimate
    {
        return DB::transaction(function () use ($estimate) {
            $items = $this->itemRepository->forEstimateOrdered($estimate);
            $materials = 0.0;
            $labor = 0.0;
            $overhead = 0.0;
            foreach ($items as $item) {
                $line = round((float) $item->quantity * (float) $item->unit_price, 2);
                $this->itemRepository->update($item, ['total_price' => $line]);
                match ($item->category) {
                    EstimateItemCategory::Material => $materials += $line,
                    EstimateItemCategory::Labor => $labor += $line,
                    EstimateItemCategory::Overhead => $overhead += $line,
                };
            }
            $subtotal = $materials + $labor + $overhead;
            $markup = (float) $estimate->markup_percentage;
            $grand = round($subtotal * (1 + $markup / 100), 2);

            return $this->estimateRepository->update($estimate->fresh() ?? $estimate, [
                'total_materials' => round($materials, 2),
                'total_labor' => round($labor, 2),
                'total_overhead' => round($overhead, 2),
                'grand_total' => $grand,
            ]);
        });
    }

    /**
     * @param  list<int>  $ids
     * @return list<array<string, mixed>>
     */
    public function compare(Project $project, array $ids): array
    {
        $rows = $this->estimateRepository->forProjectWhereIdsIn($project, $ids);
        if ($rows->count() !== count($ids)) {
            throw ValidationException::withMessages(['ids' => ['كل المعرفات يجب أن تنتمي لهذا المشروع']]);
        }

        return $rows->map(fn (Estimate $e) => [
            'id' => $e->id,
            'title' => $e->title,
            'status' => $e->status->value,
            'grand_total' => $e->grand_total,
            'items_count' => $e->items->count(),
        ])->values()->all();
    }

    public function exportCsvStream(Estimate $estimate): StreamedResponse
    {
        $estimate->load('items');

        $filename = 'estimate-'.$estimate->id.'-boq.csv';

        return response()->streamDownload(function () use ($estimate) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['description_ar', 'description_en', 'category', 'quantity', 'unit', 'unit_price', 'total_price']);
            foreach ($estimate->items as $item) {
                fputcsv($out, [
                    $item->description_ar,
                    $item->description_en,
                    $item->category->value,
                    (string) $item->quantity,
                    $item->unit,
                    (string) $item->unit_price,
                    (string) $item->total_price,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addItem(Estimate $estimate, array $data): EstimateItem
    {
        $this->assertEditable($estimate);
        $line = round((float) $data['quantity'] * (float) $data['unit_price'], 2);

        return $this->itemRepository->create([
            'estimate_id' => $estimate->id,
            'product_id' => $data['product_id'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'category' => EstimateItemCategory::from((string) $data['category']),
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'unit_price' => $data['unit_price'],
            'total_price' => $line,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateItem(Estimate $estimate, EstimateItem $item, array $data): EstimateItem
    {
        $this->assertEditable($estimate);
        if ($item->estimate_id !== $estimate->id) {
            throw ValidationException::withMessages(['item' => ['البند لا يتبع هذا التقدير']]);
        }

        $payload = [];
        foreach (['description_ar', 'description_en', 'unit', 'sort_order', 'product_id'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
            }
        }
        if (isset($data['category'])) {
            $payload['category'] = EstimateItemCategory::from((string) $data['category']);
        }
        if (isset($data['quantity'])) {
            $payload['quantity'] = $data['quantity'];
        }
        if (isset($data['unit_price'])) {
            $payload['unit_price'] = $data['unit_price'];
        }
        if (isset($data['quantity']) || isset($data['unit_price'])) {
            $qty = (float) ($payload['quantity'] ?? $item->quantity);
            $price = (float) ($payload['unit_price'] ?? $item->unit_price);
            $payload['total_price'] = round($qty * $price, 2);
        }

        return $this->itemRepository->update($item, $payload);
    }

    public function deleteItem(Estimate $estimate, EstimateItem $item): void
    {
        $this->assertEditable($estimate);
        if ($item->estimate_id !== $estimate->id) {
            throw ValidationException::withMessages(['item' => ['البند لا يتبع هذا التقدير']]);
        }
        $this->itemRepository->delete($item);
    }

    private function assertEditable(Estimate $estimate): void
    {
        if (! in_array($estimate->status, [EstimateStatus::Draft, EstimateStatus::Submitted], true)) {
            throw ValidationException::withMessages([
                'estimate' => ['لا يمكن تعديل البنود في حالة التقدير الحالية'],
            ]);
        }
    }
}
