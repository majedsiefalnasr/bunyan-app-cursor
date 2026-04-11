<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_has_five_cases(): void
    {
        $this->assertCount(5, UserRole::cases());
    }

    public function test_backing_values(): void
    {
        $this->assertSame('customer', UserRole::Customer->value);
        $this->assertSame('contractor', UserRole::Contractor->value);
        $this->assertSame('supervising_architect', UserRole::SupervisingArchitect->value);
        $this->assertSame('field_engineer', UserRole::FieldEngineer->value);
        $this->assertSame('admin', UserRole::Admin->value);
    }

    public function test_arabic_labels(): void
    {
        $this->assertSame('العميل', UserRole::Customer->label());
        $this->assertSame('المقاول', UserRole::Contractor->label());
        $this->assertSame('المهندس المشرف', UserRole::SupervisingArchitect->label());
        $this->assertSame('المهندس الميداني', UserRole::FieldEngineer->label());
        $this->assertSame('الإدارة', UserRole::Admin->label());
    }

    public function test_values_returns_all_backing_values(): void
    {
        $expected = ['customer', 'contractor', 'supervising_architect', 'field_engineer', 'admin'];
        $this->assertSame($expected, UserRole::values());
    }

    public function test_from_valid_value(): void
    {
        $this->assertSame(UserRole::Customer, UserRole::from('customer'));
        $this->assertSame(UserRole::Admin, UserRole::from('admin'));
    }

    public function test_try_from_invalid_value_returns_null(): void
    {
        $this->assertNull(UserRole::tryFrom('invalid_role'));
        $this->assertNull(UserRole::tryFrom(''));
    }
}
