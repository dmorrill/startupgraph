<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class AuditCompanyData extends Command
{
    protected $signature = 'companies:audit';

    protected $description = 'Audit company data completeness and report missing fields';

    public function handle(): int
    {
        $total = Company::count();

        $fields = [
            'description' => Company::whereNull('description')->orWhere('description', '')->count(),
            'website' => Company::whereNull('website')->orWhere('website', '')->count(),
            'category' => Company::whereNull('category')->orWhere('category', '')->count(),
            'city' => Company::whereNull('city')->orWhere('city', '')->count(),
            'country' => Company::whereNull('country')->orWhere('country', '')->count(),
            'founded_date' => Company::whereNull('founded_date')->count(),
        ];

        $this->info("Company Data Audit ({$total} total companies)");
        $this->newLine();

        $rows = [];
        foreach ($fields as $field => $missing) {
            $coverage = $total > 0 ? round((($total - $missing) / $total) * 100, 1) : 0;
            $rows[] = [$field, $missing, "{$coverage}%"];
        }

        $this->table(['Field', 'Missing', 'Coverage'], $rows);

        // List companies with the most missing fields
        $this->newLine();
        $this->info('Companies with most missing data:');

        $companies = Company::all();
        $incomplete = [];

        foreach ($companies as $company) {
            $missingCount = 0;
            foreach (array_keys($fields) as $field) {
                if (empty($company->$field)) {
                    $missingCount++;
                }
            }
            if ($missingCount > 0) {
                $incomplete[] = [
                    'name' => $company->name,
                    'missing' => $missingCount,
                    'fields' => collect(array_keys($fields))
                        ->filter(fn ($f) => empty($company->$f))
                        ->implode(', '),
                ];
            }
        }

        usort($incomplete, fn ($a, $b) => $b['missing'] <=> $a['missing']);

        $this->table(
            ['Company', 'Missing Fields', 'Which Fields'],
            array_slice(array_map(fn ($c) => [$c['name'], $c['missing'], $c['fields']], $incomplete), 0, 20)
        );

        return self::SUCCESS;
    }
}
