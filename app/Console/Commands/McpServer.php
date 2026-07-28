<?php

namespace App\Console\Commands;

use App\Services\Mcp\McpToolService;
use Illuminate\Console\Command;

/**
 * A lightweight MCP (Model Context Protocol) server that speaks JSON-RPC
 * over stdin/stdout, so AI tools like Claude can query StartupGraph directly.
 *
 * Read-only: write tools live on the authenticated hosted /mcp endpoint.
 *
 * Usage: php artisan mcp:serve
 */
class McpServer extends Command
{
    protected $signature = 'mcp:serve';

    protected $description = 'Start an MCP (Model Context Protocol) server for AI tool integration';

    public function __construct(private McpToolService $tools)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $stdin = fopen('php://stdin', 'r');
        stream_set_blocking($stdin, true);

        while (($line = fgets($stdin)) !== false) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $request = json_decode($line, true);
            if (! $request) {
                continue;
            }

            $response = $this->handleRequest($request);
            if ($response !== null) {
                fwrite(STDOUT, json_encode($response)."\n");
            }
        }

        fclose($stdin);

        return 0;
    }

    private function handleRequest(array $request): ?array
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? '';

        return match ($method) {
            'initialize' => $this->jsonRpc($id, [
                'protocolVersion' => '2024-11-05',
                'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'startupgraph', 'version' => '2.0.0'],
            ]),
            'tools/list' => $this->jsonRpc($id, ['tools' => $this->tools->tools()]),
            'tools/call' => $this->handleToolCall($id, $request['params'] ?? []),
            'notifications/initialized' => null,
            default => $this->jsonRpcError($id, -32601, "Method not found: {$method}"),
        };
    }

    private function handleToolCall(mixed $id, array $params): array
    {
        $result = $this->tools->execute($params['name'] ?? '', $params['arguments'] ?? []);

        return $this->jsonRpc($id, [
            'content' => [['type' => 'text', 'text' => json_encode($result, JSON_PRETTY_PRINT)]],
        ]);
    }

    private function jsonRpc(mixed $id, array $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    private function jsonRpcError(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
