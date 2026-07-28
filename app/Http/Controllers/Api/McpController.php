<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Mcp\McpToolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The hosted MCP endpoint (Streamable HTTP transport, JSON responses).
 * Agents connect with just a URL and a Sanctum bearer token — this is
 * the product's front door. POST carries JSON-RPC; notifications get
 * 202 Accepted; GET (SSE streaming) is not offered.
 */
class McpController extends Controller
{
    public function __construct(private McpToolService $tools) {}

    public function handle(Request $request): JsonResponse|Response
    {
        $message = $request->json()->all();

        if (! isset($message['method'])) {
            return $this->error(null, -32600, 'Invalid request: missing method');
        }

        $id = $message['id'] ?? null;
        $method = $message['method'];

        // Notifications (no id) get acknowledged without a body.
        if ($id === null) {
            return response()->noContent(202);
        }

        return match ($method) {
            'initialize' => $this->result($id, [
                'protocolVersion' => '2025-03-26',
                'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'startupgraph', 'version' => '2.0.0'],
            ]),
            'ping' => $this->result($id, (object) []),
            'tools/list' => $this->result($id, ['tools' => $this->tools->tools($request->user())]),
            'tools/call' => $this->toolCall($request, $id, $message['params'] ?? []),
            default => $this->error($id, -32601, "Method not found: {$method}"),
        };
    }

    private function toolCall(Request $request, mixed $id, array $params): JsonResponse
    {
        $result = $this->tools->execute(
            $params['name'] ?? '',
            $params['arguments'] ?? [],
            $request->user()
        );

        return $this->result($id, [
            'content' => [['type' => 'text', 'text' => json_encode($result, JSON_PRETTY_PRINT)]],
            'isError' => isset($result['error']),
        ]);
    }

    private function result(mixed $id, mixed $result): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
    }

    private function error(mixed $id, int $code, string $message): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => ['code' => $code, 'message' => $message],
        ], 400);
    }
}
