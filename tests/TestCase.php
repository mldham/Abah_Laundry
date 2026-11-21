<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function sebagaiAdmin(string $peran = User::PERAN_ADMIN): static
    {
        return $this->actingAs(User::factory()->create(['peran' => $peran]), 'admin');
    }
}
