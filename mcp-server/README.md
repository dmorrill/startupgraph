# StartupGraph MCP Server

An MCP (Model Context Protocol) server that allows Claude Code and other AI agents to query startup traction data from StartupGraph.

## Installation

### Option 1: npx (recommended)

```bash
claude mcp add startupgraph -- npx @startupgraph/mcp
```

### Option 2: Global install

```bash
npm install -g @startupgraph/mcp
claude mcp add startupgraph -- startupgraph-mcp
```

### Option 3: Local development

```bash
# Clone the repo
git clone https://github.com/dmorrill/startupgraph.git
cd startupgraph/mcp-server

# Install and build
npm install
npm run build

# Add to Claude Code
claude mcp add startupgraph-local -- node /path/to/startupgraph/mcp-server/build/index.js
```

## Configuration

### Custom API URL

By default, the server connects to `http://localhost:8000/api`. To use a different URL:

```bash
# Via command line
claude mcp add startupgraph -- npx @startupgraph/mcp --url https://startupgraph.dev/api

# Via environment variable
STARTUPGRAPH_API_URL=https://startupgraph.dev/api npx @startupgraph/mcp
```

## Available Tools

| Tool | Description |
|------|-------------|
| `search_companies` | Search for companies by name, industry, or location |
| `get_company` | Get detailed traction data for a specific company |
| `get_funding_history` | Get complete funding rounds with investors |
| `get_leadership` | Get company executives and team |
| `get_headcount_history` | Get employee count over time |
| `compare_companies` | Compare 2-5 companies side-by-side |
| `list_categories` | List available category filters |
| `get_stats` | Get database statistics |

## Example Usage

Once installed, you can ask Claude Code questions like:

- "What's Stripe's funding history?"
- "Compare OpenAI and Anthropic"
- "Find AI companies in San Francisco"
- "Who are the executives at Figma?"
- "How has Notion's headcount grown?"

## Development

```bash
cd mcp-server

# Install dependencies
npm install

# Build
npm run build

# Watch mode for development
npm run dev
```

## License

MIT
