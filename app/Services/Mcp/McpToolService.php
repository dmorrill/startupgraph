<?php

namespace App\Services\Mcp;

use App\Models\Company;
use App\Models\OpenSourceProject;
use App\Models\Screen;
use App\Models\Signal;
use App\Models\User;
use App\Services\CompanyQueryService;

/**
 * The MCP tool registry and executor, shared by the stdio server
 * (php artisan mcp:serve, read-only) and the hosted /mcp HTTP endpoint
 * (authenticated, read + write). Write tools operate on the calling
 * user's research layer and record provenance via $createdVia.
 */
class McpToolService
{
    public function __construct(private CompanyQueryService $companyQuery) {}

    /**
     * Tool definitions. Write tools are only advertised when the caller
     * is authenticated (i.e. over the hosted endpoint).
     */
    public function tools(?User $user = null): array
    {
        $tools = [
            [
                'name' => 'search_companies',
                'description' => 'Search the StartupGraph database for companies by name, category, or country.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Search query (name or description)'],
                        'category' => ['type' => 'string', 'description' => 'Category filter (ai_ml, fintech, etc.)'],
                        'country' => ['type' => 'string', 'description' => 'Country filter'],
                        'funded_recent' => ['type' => 'string', 'description' => 'Only companies funded recently: 3m, 6m, 1y, or 2y'],
                        'limit' => ['type' => 'integer', 'description' => 'Max results (default 10)', 'default' => 10],
                    ],
                ],
            ],
            [
                'name' => 'get_company',
                'description' => 'Get detailed info about a specific company by slug or name.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'slug' => ['type' => 'string', 'description' => 'Company slug or name'],
                    ],
                    'required' => ['slug'],
                ],
            ],
            [
                'name' => 'get_stats',
                'description' => 'Get overall StartupGraph database statistics.',
                'inputSchema' => ['type' => 'object', 'properties' => []],
            ],
            [
                'name' => 'search_oss_projects',
                'description' => 'Search open source projects tracked in StartupGraph.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Search query'],
                        'language' => ['type' => 'string', 'description' => 'Programming language filter'],
                        'limit' => ['type' => 'integer', 'default' => 10],
                    ],
                ],
            ],
        ];

        if ($user) {
            $tools = array_merge($tools, [
                [
                    'name' => 'create_list',
                    'description' => 'Create a named list of companies in your research layer (or fetch it if it already exists).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'List name, e.g. "AI infra watchlist"'],
                            'description' => ['type' => 'string', 'description' => 'What this list is for'],
                        ],
                        'required' => ['name'],
                    ],
                ],
                [
                    'name' => 'add_to_list',
                    'description' => 'Add a company to one of your lists (created if missing), with an optional rationale for why it belongs there.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'list' => ['type' => 'string', 'description' => 'List name'],
                            'company' => ['type' => 'string', 'description' => 'Company slug'],
                            'rationale' => ['type' => 'string', 'description' => 'Why this company is on the list'],
                        ],
                        'required' => ['list', 'company'],
                    ],
                ],
                [
                    'name' => 'save_note',
                    'description' => 'Attach a research memo (markdown) to a company in your research layer.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'company' => ['type' => 'string', 'description' => 'Company slug'],
                            'title' => ['type' => 'string', 'description' => 'Short title for the memo'],
                            'body' => ['type' => 'string', 'description' => 'The memo body (markdown)'],
                        ],
                        'required' => ['company', 'body'],
                    ],
                ],
                [
                    'name' => 'create_screen',
                    'description' => 'Save a screen: a named query over the company graph that stores a result snapshot. Criteria: q, category, country, funded_after, funded_before, funded_recent (3m|6m|1y|2y), sort, order.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Screen name, e.g. "Recently funded dev tools"'],
                            'criteria' => ['type' => 'object', 'description' => 'Query criteria object'],
                        ],
                        'required' => ['name', 'criteria'],
                    ],
                ],
                [
                    'name' => 'refresh_screen',
                    'description' => 'Re-run one of your screens and update its stored snapshot.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Screen name'],
                        ],
                        'required' => ['name'],
                    ],
                ],
                [
                    'name' => 'log_signal',
                    'description' => 'Log a custom signal to your feed — something noteworthy you found, optionally tied to a company.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'One-line summary'],
                            'body' => ['type' => 'string', 'description' => 'Detail (markdown)'],
                            'company' => ['type' => 'string', 'description' => 'Company slug (optional)'],
                        ],
                        'required' => ['title'],
                    ],
                ],
                [
                    'name' => 'list_my_research',
                    'description' => 'Summarize your research layer: lists, screens, and recent notes.',
                    'inputSchema' => ['type' => 'object', 'properties' => []],
                ],
            ]);
        }

        return $tools;
    }

    public function execute(string $name, array $args, ?User $user = null): array
    {
        $readResult = match ($name) {
            'search_companies' => $this->searchCompanies($args),
            'get_company' => $this->getCompany($args),
            'get_stats' => $this->getStats(),
            'search_oss_projects' => $this->searchOssProjects($args),
            default => null,
        };

        if ($readResult !== null) {
            return $readResult;
        }

        if (! $user) {
            return ['error' => "Unknown tool: {$name}. Write tools require the authenticated /mcp endpoint."];
        }

        return match ($name) {
            'create_list' => $this->createList($args, $user),
            'add_to_list' => $this->addToList($args, $user),
            'save_note' => $this->saveNote($args, $user),
            'create_screen' => $this->createScreen($args, $user),
            'refresh_screen' => $this->refreshScreen($args, $user),
            'log_signal' => $this->logSignal($args, $user),
            'list_my_research' => $this->listMyResearch($user),
            default => ['error' => "Unknown tool: {$name}"],
        };
    }

    // ------------------------------------------------------------------
    // Read tools
    // ------------------------------------------------------------------

    private function searchCompanies(array $args): array
    {
        $criteria = [
            'q' => $args['query'] ?? null,
            'category' => $args['category'] ?? null,
            'country' => $args['country'] ?? null,
            'funded_recent' => $args['funded_recent'] ?? null,
        ];

        return $this->companyQuery->build(array_filter($criteria))
            ->limit(min($args['limit'] ?? 10, 50))
            ->get(['id', 'name', 'slug', 'website', 'description', 'category', 'city', 'country', 'current_headcount'])
            ->makeHidden('id')
            ->toArray();
    }

    private function getCompany(array $args): array
    {
        $company = Company::where('slug', $args['slug'] ?? '')
            ->orWhere('name', 'like', $args['slug'] ?? '')
            ->with(['fundingRounds.investors', 'headcountSnapshots', 'people'])
            ->withSum('fundingRounds', 'amount')
            ->first();

        return $company ? $company->toArray() : ['error' => 'Company not found'];
    }

    private function getStats(): array
    {
        return [
            'companies' => Company::count(),
            'oss_projects' => OpenSourceProject::count(),
            'categories' => Company::CATEGORIES,
        ];
    }

    private function searchOssProjects(array $args): array
    {
        $query = OpenSourceProject::query();

        if ($q = ($args['query'] ?? null)) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where('name', 'like', "%{$escaped}%");
        }
        if ($lang = ($args['language'] ?? null)) {
            $query->where('primary_language', $lang);
        }

        return $query->orderByDesc('stars')
            ->limit(min($args['limit'] ?? 10, 50))
            ->get()
            ->toArray();
    }

    // ------------------------------------------------------------------
    // Write tools (user-scoped, provenance-stamped)
    // ------------------------------------------------------------------

    private function createdVia(User $user): string
    {
        return 'mcp:'.($user->currentAccessToken()?->name ?? 'unknown');
    }

    private function createList(array $args, User $user): array
    {
        if (empty($args['name'])) {
            return ['error' => 'name is required'];
        }

        $list = $user->lists()->firstOrCreate(
            ['name' => $args['name']],
            ['description' => $args['description'] ?? null, 'created_via' => $this->createdVia($user)]
        );

        return [
            'id' => $list->id,
            'name' => $list->name,
            'created' => $list->wasRecentlyCreated,
        ];
    }

    private function addToList(array $args, User $user): array
    {
        if (empty($args['list']) || empty($args['company'])) {
            return ['error' => 'list and company are required'];
        }

        $company = Company::where('slug', $args['company'])->first();
        if (! $company) {
            return ['error' => "Company not found: {$args['company']}"];
        }

        $list = $user->lists()->firstOrCreate(
            ['name' => $args['list']],
            ['created_via' => $this->createdVia($user)]
        );

        $entry = $list->entries()->firstOrCreate(
            ['company_id' => $company->id],
            ['rationale' => $args['rationale'] ?? null, 'created_via' => $this->createdVia($user)]
        );

        $list->touch();

        return [
            'list' => $list->name,
            'company' => $company->slug,
            'added' => $entry->wasRecentlyCreated,
            'rationale' => $entry->rationale,
        ];
    }

    private function saveNote(array $args, User $user): array
    {
        if (empty($args['company']) || empty($args['body'])) {
            return ['error' => 'company and body are required'];
        }

        $company = Company::where('slug', $args['company'])->first();
        if (! $company) {
            return ['error' => "Company not found: {$args['company']}"];
        }

        $note = $user->notes()->create([
            'company_id' => $company->id,
            'title' => $args['title'] ?? null,
            'body' => $args['body'],
            'created_via' => $this->createdVia($user),
        ]);

        return ['id' => $note->id, 'company' => $company->slug, 'title' => $note->title];
    }

    private function createScreen(array $args, User $user): array
    {
        if (empty($args['name']) || empty($args['criteria']) || ! is_array($args['criteria'])) {
            return ['error' => 'name and criteria (object) are required'];
        }

        $screen = $user->screens()->updateOrCreate(
            ['name' => $args['name']],
            ['criteria' => CompanyQueryService::sanitizeCriteria($args['criteria']), 'created_via' => $this->createdVia($user)]
        );

        return $this->runScreen($screen);
    }

    private function refreshScreen(array $args, User $user): array
    {
        $screen = $user->screens()->where('name', $args['name'] ?? '')->first();
        if (! $screen) {
            return ['error' => "Screen not found: {$args['name']}"];
        }

        return $this->runScreen($screen);
    }

    private function runScreen(Screen $screen): array
    {
        $companies = $this->companyQuery->build($screen->criteria ?? [])->limit(100)->get();

        $snapshot = $companies->map(fn ($company) => [
            'name' => $company->name,
            'slug' => $company->slug,
            'category' => $company->category,
            'city' => $company->city,
            'country' => $company->country,
            'current_headcount' => $company->current_headcount,
            'total_raised' => (float) ($company->funding_rounds_sum_amount ?? 0),
            'latest_funding_date' => $company->latest_funding_date,
        ])->values()->all();

        $screen->update([
            'snapshot' => $snapshot,
            'result_count' => count($snapshot),
            'refreshed_at' => now(),
        ]);

        return [
            'screen' => $screen->name,
            'result_count' => count($snapshot),
            'results' => array_slice($snapshot, 0, 25),
        ];
    }

    private function logSignal(array $args, User $user): array
    {
        if (empty($args['title'])) {
            return ['error' => 'title is required'];
        }

        $companyId = null;
        if (! empty($args['company'])) {
            $companyId = Company::where('slug', $args['company'])->value('id');
        }

        $signal = $user->signals()->create([
            'company_id' => $companyId,
            'type' => Signal::TYPE_CUSTOM,
            'title' => $args['title'],
            'body' => $args['body'] ?? null,
            'created_via' => $this->createdVia($user),
        ]);

        return ['id' => $signal->id, 'title' => $signal->title];
    }

    private function listMyResearch(User $user): array
    {
        return [
            'lists' => $user->lists()->withCount('entries')->get()
                ->map(fn ($list) => ['name' => $list->name, 'companies' => $list->entries_count])->all(),
            'screens' => $user->screens()->get()
                ->map(fn ($screen) => [
                    'name' => $screen->name,
                    'result_count' => $screen->result_count,
                    'refreshed_at' => $screen->refreshed_at?->toIso8601String(),
                ])->all(),
            'recent_notes' => $user->notes()->with('company:id,name,slug')->latest()->limit(10)->get()
                ->map(fn ($note) => [
                    'company' => $note->company?->slug,
                    'title' => $note->title,
                    'created_at' => $note->created_at?->toIso8601String(),
                ])->all(),
        ];
    }
}
