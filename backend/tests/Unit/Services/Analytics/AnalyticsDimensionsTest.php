<?php

namespace Tests\Unit\Services\Analytics;

use App\Services\Analytics\AnalyticsDimensions;
use PHPUnit\Framework\TestCase;

final class AnalyticsDimensionsTest extends TestCase
{
    public function test_hash_is_stable_for_key_ordering(): void
    {
        $a = ['b' => 2, 'a' => 1];
        $b = ['a' => 1, 'b' => 2];

        self::assertSame(AnalyticsDimensions::hash($a), AnalyticsDimensions::hash($b));
    }
}
