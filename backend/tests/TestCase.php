<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAs($user, $guard = null)
    {
        if ($guard === null) {
            Sanctum::actingAs($user);

            return $this;
        }

        return parent::actingAs($user, $guard);
    }
}
