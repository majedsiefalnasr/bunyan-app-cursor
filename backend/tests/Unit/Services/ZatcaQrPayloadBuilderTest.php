<?php

namespace Tests\Unit\Services;

use App\Services\ZatcaQrPayloadBuilder;
use PHPUnit\Framework\TestCase;

class ZatcaQrPayloadBuilderTest extends TestCase
{
    public function test_build_base64_matches_tlv_encoding(): void
    {
        $builder = new ZatcaQrPayloadBuilder;

        $expected = base64_encode(
            chr(1).chr(1).'A'
            .chr(2).chr(1).'B'
            .chr(3).chr(1).'C'
            .chr(4).chr(4).'1.00'
            .chr(5).chr(4).'0.15'
        );

        $this->assertSame(
            $expected,
            $builder->buildBase64('A', 'B', 'C', '1.00', '0.15'),
        );
    }
}
