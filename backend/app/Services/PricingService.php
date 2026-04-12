<?php

namespace App\Services;

use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\PriceTierRepository;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PricingService
{
    public function __construct(private PriceTierRepository $tiers)
    {
    }

    public function listTiers(Product $product): Collection
    {
        return $product->priceTiers()
            ->orderBy('product_variant_id')
            ->orderBy('min_quantity')
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    public function syncTiers(Product $product, array $tiers): void
    {
        $normalized = [];
        foreach ($tiers as $row) {
            $vid = $row['product_variant_id'] ?? null;
            if ($vid !== null) {
                $variant = ProductVariant::query()->where('id', $vid)->where('product_id', $product->id)->first();
                if ($variant === null) {
                    throw ValidationException::withMessages([
                        'tiers' => ['المتغير لا يتبع هذا المنتج'],
                    ]);
                }
            }
            $normalized[] = [
                'product_variant_id' => $vid,
                'min_quantity' => (int) $row['min_quantity'],
                'max_quantity' => isset($row['max_quantity']) && $row['max_quantity'] !== null ? (int) $row['max_quantity'] : null,
                'unit_price' => number_format((float) $row['unit_price'], 2, '.', ''),
            ];
        }

        $this->assertBandsValid($normalized);

        $this->tiers->replaceForProduct($product->id, $normalized);
    }

    /**
     * @param  array<int, array{product_variant_id: int|null, min_quantity: int, max_quantity: int|null, unit_price: string}>  $bands
     */
    private function assertBandsValid(array $bands): void
    {
        $byKey = [];
        foreach ($bands as $b) {
            $key = $b['product_variant_id'] === null ? 'p' : 'v:'.$b['product_variant_id'];
            $byKey[$key][] = $b;
        }

        foreach ($byKey as $group) {
            usort($group, fn ($a, $b) => $a['min_quantity'] <=> $b['min_quantity']);
            $n = count($group);
            for ($i = 0; $i < $n; $i++) {
                $min = $group[$i]['min_quantity'];
                $max = $group[$i]['max_quantity'];
                if ($max !== null && $max < $min) {
                    throw ValidationException::withMessages([
                        'tiers' => ['مدى الكمية غير صالح'],
                    ]);
                }
                if ($i < $n - 1) {
                    if ($max === null) {
                        throw ValidationException::withMessages([
                            'tiers' => ['يجب أن يكون النطاق غير المحدود الأخير فقط'],
                        ]);
                    }
                    $nextMin = $group[$i + 1]['min_quantity'];
                    if ($nextMin <= $max) {
                        throw ValidationException::withMessages([
                            'tiers' => ['تداخل في نطاقات الكمية'],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @return array{unit_price: string, line_total: string, currency: string}
     */
    public function calculate(Product $product, ?int $variantId, int $quantity): array
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => ['الكمية يجب أن تكون 1 على الأقل']]);
        }

        $variant = null;
        if ($variantId !== null) {
            $variant = ProductVariant::query()
                ->where('id', $variantId)
                ->where('product_id', $product->id)
                ->first();
            if ($variant === null) {
                throw ValidationException::withMessages([
                    'product_variant_id' => ['المتغير غير موجود لهذا المنتج'],
                ]);
            }
        }

        $unit = $this->resolveUnitPrice($product, $variant, $quantity);
        $line = number_format((float) $unit * $quantity, 2, '.', '');

        return [
            'unit_price' => $unit,
            'line_total' => $line,
            'currency' => 'SAR',
        ];
    }

    private function resolveUnitPrice(Product $product, ?ProductVariant $variant, int $quantity): string
    {
        if ($variant !== null) {
            $variantTiers = $product->priceTiers()
                ->where('product_variant_id', $variant->id)
                ->orderBy('min_quantity')
                ->get();
            $hit = $this->matchTier($variantTiers, $quantity);
            if ($hit !== null) {
                return number_format((float) $hit->unit_price, 2, '.', '');
            }
        }

        $productTiers = $product->priceTiers()
            ->whereNull('product_variant_id')
            ->orderBy('min_quantity')
            ->get();
        $hit = $this->matchTier($productTiers, $quantity);
        if ($hit !== null) {
            return number_format((float) $hit->unit_price, 2, '.', '');
        }

        $base = (float) $product->price;
        $mod = $variant ? (float) $variant->price_modifier : 0.0;

        return number_format($base + $mod, 2, '.', '');
    }

    /**
     * @param  Collection<int, PriceTier>  $tiers
     */
    private function matchTier(Collection $tiers, int $quantity): ?PriceTier
    {
        foreach ($tiers as $tier) {
            $min = (int) $tier->min_quantity;
            $max = $tier->max_quantity !== null ? (int) $tier->max_quantity : null;
            if ($quantity < $min) {
                continue;
            }
            if ($max !== null && $quantity > $max) {
                continue;
            }

            return $tier;
        }

        return null;
    }
}
