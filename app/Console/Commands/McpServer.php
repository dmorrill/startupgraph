<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\OpenSourceProject;
use Illuminate\Console\Command;

/**
 * A lightweight MCP (Model Context Protocol) server that speaks JSON-RPC
 * over stdin/stdout, so AI tools like Claude can query StartupGraph directly.
 *
 * Usage: php artisan mcp:serve
 */
class McpServer extends Command
{
    protected $signature = 'mcp:serve';
    protected $description = 'Start an MCP (Model Context Protocol) server for AI tool integration';

    private array $tools = [
        [
            'name' => 'search_companies',
            'description' => 'Search the StartupGraph database for companies by name, category, or country.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Search query (name or description)'],
                    'category' => ['type' => 'string', 'description' => 'Category filter (ai_ml, fintech, etc.)'],
                    'country' => ['type' => 'string', 'description' => 'Country filter'],
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

    public function handle(): int
    {
        $stdin = fopen('php://stdin', 'r');
        stream_set_blocking($stdin, true);

        while (($line = fgets($stdin)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;

            $request = json_decode($line, true);
            if (! $request) continue;

            $response = $this->handleRequest($request);
            fwrite(STDOUT, json_encode($response) . "\n");
        }

        fclose($stdin);
        return 0;
    }

    private function handleRequest(array $request): array
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? '';

        return match ($method) {
            'initialize' => $this->jsonRpc($id, [
                'protocolVersion' => '2024-11-05',
                'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'startupgraph', 'version' => '1.0.0'],
            ]),
            'tools/list' => $this->jsonRpc($id, ['tools' => $this->tools]),
            'tools/call' => $this->handleToolCall($id, $request['params'] ?? []),
            'notifications/initialized' => ['jsonrpc' => '2.0'],
            default => $this->jsonRpcError($id, -32601, "Method not found: {$method}"),
        };
    }

    private function handleToolCall(?string $id, array $params): array
    {
        $name = $params['name'] ?? '';
        $args = $params['arguments'] ?? [];

        $result = match ($name) {
            'search_companies' => $this->searchCompanies($args),
            'get_company' => $this->getCompany($args),
            'get_stats' => $this->getStats(),
            'search_oss_projects' => $this->searchOssProjects($args),
            default => ['error' => "Unknown tool: {$name}"],
        };

        return $this->jsonRpc($id, [
            'content' => [['type' => 'text', 'text' => json_encode($result, JSON_PRETTY_PRINT)]],
        ]);
    }

    private function searchCompanies(array $args): array
    {
        $query = Company::query()
            ->withSum('fundingRounds', 'amount')
            ->withCount('fundingRounds');

        if ($q = ($args['query'] ?? null)) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where(function ($qb) use ($escaped) {
                $qb->where('name', 'like', "%{$escaped}%")
                   ->orWhere('description', 'like', "%{$escaped}%");
            });
        }

        if ($cat = ($args['category'] ?? null)) $query->where('category', $cat);
        if ($country = ($args['country'] ?? null)) $query->where('country', $country);

        return $query->orderBy('name')
            ->limit(min($args['limit'] ?? 10, 50))
            ->get(['name', 'slug', 'website', 'description', 'category', 'city', 'country', 'current_headcount'])
            ->toArray();
    }

    private function getCompany(array $args): array
    {
        $company = Company::where('slug', $args['slug'])
            ->orWhere('name', 'like', $args['slug'])
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

    private function jsonRpc(?string $id, array $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    private function jsonRpcError(?string $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
