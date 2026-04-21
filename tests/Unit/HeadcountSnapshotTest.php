<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeadcountSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_headcount_snapshot_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $snapshot = HeadcountSnapshot::factory()->create(['company_id' => $company->id]);
        $this->assertInstanceOf(Company::class, $snapshot->company);
    }

    public function test_headcount_snapshot_has_count(): void
    {
        $snapshot = HeadcountSnapshot::factory()->create(['headcount' => 150]);
        $this->assertEquals(150, $snapshot->headcount);
    }
}
