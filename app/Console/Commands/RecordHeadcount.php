<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use Illuminate\Console\Command;

class RecordHeadcount extends Command
{
    protected $signature = 'company:headcount
                            {company : Company slug or name}
                            {headcount : Number of employees}
                            {--date= : Date of observation (defaults to today)}
                            {--source=linkedin : Source of the data}';

    protected $description = 'Record a headcount snapshot for a company';

    public function handle(): int
    {
        $companyInput = $this->argument('company');
        $headcount = (int) $this->argument('headcount');
        $date = $this->option('date') ? date('Y-m-d', strtotime($this->option('date'))) : date('Y-m-d');
        $source = $this->option('source');

        // Find company by slug or name
        $company = Company::where('slug', $companyInput)
            ->orWhere('name', 'like', "%{$companyInput}%")
            ->first();

        if (! $company) {
            $this->error("Company not found: {$companyInput}");

            return 1;
        }

        // Create the snapshot
        $snapshot = HeadcountSnapshot::create([
            'company_id' => $company->id,
            'headcount' => $headcount,
            'recorded_date' => $date,
            'source' => $source,
        ]);

        // Update current_headcount on company if this is the most recent snapshot
        $latestSnapshot = $company->headcountSnapshots()
            ->orderBy('recorded_date', 'desc')
            ->first();

        if ($latestSnapshot && $latestSnapshot->id === $snapshot->id) {
            $company->update(['current_headcount' => $headcount]);
        }

        $this->info("Recorded headcount for {$company->name}:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Company', $company->name],
                ['Headcount', number_format($headcount)],
                ['Date', $date],
                ['Source', $source],
            ]
        );

        // Show headcount history
        $history = $company->headcountSnapshots()
            ->orderBy('recorded_date', 'desc')
            ->limit(5)
            ->get();

        if ($history->count() > 1) {
            $this->newLine();
            $this->info('Recent history:');
            $this->table(
                ['Date', 'Headcount', 'Source'],
                $history->map(fn ($s) => [
                    $s->recorded_date->format('Y-m-d'),
                    number_format($s->headcount),
                    $s->source,
                ])->toArray()
            );
        }

        return 0;
    }
}
