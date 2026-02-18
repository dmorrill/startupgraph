<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeadcountSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created_with_factory(): void
    {
        $snapshot = HeadcountSnapshot::factory()->create();
        $this->assertDatabaseHas('headcount_snapshots', ['id' => $snapshot->id]);
    }

    public function test_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $snapshot = HeadcountSnapshot::factory()->create(['company_id' => $company->id]);
        $this->assertEquals($company->id, $snapshot->company->id);
    }

    public function test_recorded_date_cast(): void
    {
        $snapshot = HeadcountSnapshot::factory()->create(['recorded_date' => '2024-06-01']);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $snapshot->recorded_date);
    }
}
