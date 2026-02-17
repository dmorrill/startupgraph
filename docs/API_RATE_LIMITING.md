# API Rate Limiting

## Overview

The StartupGraph API is public and does not require authentication. To ensure fair usage and availability for all users, rate limiting is applied.

## Current Limits

| Tier | Limit | Window | Applies To |
|------|-------|--------|------------|
| Default | 60 requests | Per minute | All endpoints |
| Search | 30 requests | Per minute | `/api/search` |
| Export | 5 requests | Per minute | `/api/companies/export.csv` |

Rate limits are applied per IP address.

## Response Headers

Every API response includes rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 57
X-RateLimit-Reset: 1706745600
```

| Header | Description |
|--------|-------------|
| `X-RateLimit-Limit` | Maximum requests allowed in the current window |
| `X-RateLimit-Remaining` | Requests remaining in the current window |
| `X-RateLimit-Reset` | Unix timestamp when the rate limit window resets |

## When Rate Limited

If you exceed the rate limit, you'll receive a `429 Too Many Requests` response:

```json
{
    "message": "Too Many Attempts.",
    "retry_after": 42
}
```

The `Retry-After` header indicates how many seconds to wait before retrying.

## Best Practices

1. **Cache responses** — Company data doesn't change frequently. Cache for at least 5 minutes.
2. **Use pagination** — Don't fetch all companies at once. Use `per_page` and `page` params.
3. **Batch requests wisely** — Use the compare endpoint instead of multiple individual fetches.
4. **Respect Retry-After** — When rate limited, wait the indicated time before retrying.
5. **Use the CSV export** — For bulk data needs, use `/api/companies/export.csv` instead of paginating through all results.

## For AI Agents

If you're an AI agent (Claude, GPT, etc.) accessing this API:

- The MCP server (`php artisan mcp:serve`) provides a more efficient interface
- Prefer search over browsing when looking for specific companies
- Cache company slugs to avoid repeated lookups

## Requesting Higher Limits

If you need higher rate limits for a legitimate use case, please open an issue on GitHub describing your use case.
