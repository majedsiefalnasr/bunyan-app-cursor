<?php

namespace Tests\Feature;

use Tests\TestCase;

class CiReadyRouteTest extends TestCase
{
    public function test_returns_ok_in_testing_environment(): void
    {
        $this->get('/__ci_ready')
            ->assertOk()
            ->assertSee('ok', false);
    }
}
