<?php

namespace Tests\Unit\Repositories;

use App\Enums\SupplierVerificationStatus;
use App\Models\SupplierProfile;
use App\Repositories\SupplierProfileRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProfileRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SupplierProfileRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(SupplierProfileRepository::class);
    }

    public function test_paginate_verified_catalog_filters_verified_and_search_and_city(): void
    {
        $verifiedRiyadh = SupplierProfile::factory()->verified()->create([
            'city' => 'Riyadh',
            'company_name_ar' => 'شركة الاسمنت',
            'company_name_en' => 'Cement Co',
        ]);
        SupplierProfile::factory()->verified()->create([
            'city' => 'Jeddah',
            'company_name_ar' => 'شركة الحديد',
            'company_name_en' => 'Steel Co',
        ]);
        SupplierProfile::factory()->create([
            'verification_status' => SupplierVerificationStatus::Pending->value,
            'city' => 'Riyadh',
            'company_name_ar' => 'غير معتمد',
            'company_name_en' => 'Not Verified',
        ]);

        $page = $this->repo->paginateVerifiedCatalog([
            'city' => 'Riyadh',
            'search' => 'Cement',
            'per_page' => 50,
        ]);

        $this->assertSame(1, $page->total());
        $this->assertSame($verifiedRiyadh->id, $page->items()[0]->id);
    }

    public function test_update_verification_sets_verified_at_only_when_verified(): void
    {
        $profile = SupplierProfile::factory()->create([
            'verification_status' => SupplierVerificationStatus::Pending->value,
            'verified_at' => null,
        ]);

        $verified = $this->repo->updateVerification($profile, SupplierVerificationStatus::Verified);
        $this->assertSame(SupplierVerificationStatus::Verified, $verified->verification_status);
        $this->assertNotNull($verified->verified_at);

        $unverified = $this->repo->updateVerification($verified, SupplierVerificationStatus::Pending);
        $this->assertSame(SupplierVerificationStatus::Pending, $unverified->verification_status);
        $this->assertNull($unverified->verified_at);
    }

    public function test_verified_ids_helpers(): void
    {
        $a = SupplierProfile::factory()->verified()->create();
        $b = SupplierProfile::factory()->create(['verification_status' => SupplierVerificationStatus::Pending->value]);

        $ids = $this->repo->verifiedIdsByIds([$a->id, $b->id]);
        $this->assertSame([$a->id], $ids);

        $all = $this->repo->allVerifiedIds();
        $this->assertContains($a->id, $all);
        $this->assertNotContains($b->id, $all);
    }
}
