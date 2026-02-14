#!/usr/bin/env node

import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";
import { StartupGraphClient } from "./client.js";

// Parse command line arguments
const args = process.argv.slice(2);
let apiUrl = process.env.STARTUPGRAPH_API_URL || "http://localhost:8000/api";

for (let i = 0; i < args.length; i++) {
  if (args[i] === "--url" && args[i + 1]) {
    apiUrl = args[i + 1];
    i++;
  }
}

const client = new StartupGraphClient(apiUrl);

const server = new McpServer({
  name: "startupgraph",
  version: "1.0.0",
});

// Tool: search_companies
server.tool(
  "search_companies",
  "Search for startup companies by name, industry, or location. Returns matching companies with funding and headcount data.",
  {
    query: z.string().describe("Search query (company name, industry keyword, city, or country)"),
    category: z.string().optional().describe("Filter by category: ai_ml, fintech, enterprise, healthcare, robotics, space, climate, consumer, developer_tools, defense"),
    country: z.string().optional().describe("Filter by country name"),
    limit: z.number().optional().default(10).describe("Maximum results to return (default: 10, max: 50)"),
  },
  async ({ query, category, country, limit }) => {
    try {
      const response = await client.searchCompanies({
        q: query,
        category,
        country,
        limit: Math.min(limit || 10, 50),
      });

      const { companies, people } = response.data;

      let text = `Found ${companies.length} companies`;
      if (people.length > 0) {
        text += ` and ${people.length} people`;
      }
      text += ` matching "${query}":\n\n`;

      if (companies.length > 0) {
        text += "## Companies\n\n";
        for (const company of companies) {
          text += `**${company.name}** (${company.slug})\n`;
          if (company.category_label) text += `  Category: ${company.category_label}\n`;
          if (company.location.city || company.location.country) {
            text += `  Location: ${[company.location.city, company.location.country].filter(Boolean).join(", ")}\n`;
          }
          if (company.total_funding_formatted) text += `  Total Funding: ${company.total_funding_formatted}\n`;
          if (company.current_headcount) text += `  Employees: ${company.current_headcount}\n`;
          text += "\n";
        }
      }

      if (people.length > 0) {
        text += "## People\n\n";
        for (const person of people) {
          text += `**${person.name}** (${person.slug})`;
          if (person.current_role && person.current_company) {
            text += ` - ${person.current_role} at ${person.current_company}`;
          }
          text += "\n";
        }
      }

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error searching companies: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: get_company
server.tool(
  "get_company",
  "Get detailed traction data for a specific company including funding history, employee count, leadership, and product highlights.",
  {
    slug: z.string().describe("Company slug (URL-friendly identifier, e.g., 'stripe' or 'openai')"),
  },
  async ({ slug }) => {
    try {
      const response = await client.getCompany(slug);
      const company = response.data;

      let text = `# ${company.name}\n\n`;

      if (company.website) text += `Website: ${company.website}\n`;
      if (company.description) text += `\n${company.description}\n`;

      text += "\n## Overview\n\n";
      if (company.category_label) text += `- **Category:** ${company.category_label}\n`;
      if (company.founded_date) text += `- **Founded:** ${company.founded_date}\n`;
      if (company.location.city || company.location.country) {
        text += `- **Location:** ${[company.location.city, company.location.state, company.location.country].filter(Boolean).join(", ")}\n`;
      }
      if (company.current_headcount) text += `- **Employees:** ${company.current_headcount}\n`;
      if (company.linkedin_url) text += `- **LinkedIn:** ${company.linkedin_url}\n`;

      text += "\n## Funding\n\n";
      if (company.total_funding_formatted) {
        text += `- **Total Raised:** ${company.total_funding_formatted}\n`;
      }
      text += `- **Funding Rounds:** ${company.funding_rounds_count}\n`;
      if (company.latest_funding) {
        const lf = company.latest_funding;
        text += `- **Latest Round:** ${lf.round_type || "Unknown"}`;
        if (lf.amount_formatted) text += ` (${lf.amount_formatted})`;
        if (lf.announced_date) text += ` on ${lf.announced_date}`;
        text += "\n";
      }

      if (company.product_highlights && company.product_highlights.length > 0) {
        text += "\n## Product Highlights\n\n";
        for (const highlight of company.product_highlights) {
          text += `- ${highlight}\n`;
        }
      }

      if (company.people && company.people.length > 0) {
        text += "\n## Leadership\n\n";
        const current = company.people.filter(p => p.is_current !== false);
        for (const person of current.slice(0, 5)) {
          text += `- **${person.name}**`;
          if (person.role) text += ` - ${person.role}`;
          text += "\n";
        }
        if (current.length > 5) {
          text += `- ... and ${current.length - 5} more\n`;
        }
      }

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error fetching company: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: get_funding_history
server.tool(
  "get_funding_history",
  "Get complete funding history for a company including all rounds, amounts, dates, and investors.",
  {
    slug: z.string().describe("Company slug (e.g., 'stripe')"),
  },
  async ({ slug }) => {
    try {
      const response = await client.getCompanyFunding(slug);
      const data = response.data;

      let text = `# Funding History: ${data.company_name}\n\n`;
      text += `**Total Raised:** ${data.total_funding_formatted}\n`;
      text += `**Rounds:** ${data.rounds_count}\n\n`;

      if (data.funding_rounds.length > 0) {
        text += "## Rounds\n\n";
        for (const round of data.funding_rounds) {
          text += `### ${round.round_type || "Unknown Round"}`;
          if (round.announced_date) text += ` (${round.announced_date})`;
          text += "\n";

          if (round.amount_formatted) text += `- **Amount:** ${round.amount_formatted}\n`;
          if (round.pre_money_valuation) text += `- **Pre-money Valuation:** $${(round.pre_money_valuation / 1_000_000).toFixed(0)}M\n`;

          if (round.investors && round.investors.length > 0) {
            const leads = round.investors.filter(i => i.is_lead);
            const others = round.investors.filter(i => !i.is_lead);

            if (leads.length > 0) {
              text += `- **Lead Investors:** ${leads.map(i => i.name).join(", ")}\n`;
            }
            if (others.length > 0) {
              text += `- **Investors:** ${others.map(i => i.name).join(", ")}\n`;
            }
          }

          if (round.source_url) text += `- **Source:** ${round.source_url}\n`;
          text += "\n";
        }
      }

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error fetching funding history: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: get_leadership
server.tool(
  "get_leadership",
  "Get the leadership team and executives of a company.",
  {
    slug: z.string().describe("Company slug (e.g., 'stripe')"),
  },
  async ({ slug }) => {
    try {
      const response = await client.getCompanyPeople(slug);
      const data = response.data;

      let text = `# Leadership: ${data.company_name}\n\n`;
      text += `**Total:** ${data.total_count} people (${data.current_count} current)\n\n`;

      if (data.current.length > 0) {
        text += "## Current Team\n\n";
        for (const person of data.current) {
          text += `- **${person.name}**`;
          if (person.role) text += ` - ${person.role}`;
          if (person.linkedin_url) text += ` ([LinkedIn](${person.linkedin_url}))`;
          text += "\n";
        }
      }

      if (data.former.length > 0) {
        text += "\n## Former\n\n";
        for (const person of data.former) {
          text += `- **${person.name}**`;
          if (person.role) text += ` - ${person.role}`;
          text += "\n";
        }
      }

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error fetching leadership: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: get_headcount_history
server.tool(
  "get_headcount_history",
  "Get employee headcount history and growth metrics for a company.",
  {
    slug: z.string().describe("Company slug (e.g., 'stripe')"),
  },
  async ({ slug }) => {
    try {
      const response = await client.getCompanyHeadcount(slug);
      const data = response.data;

      let text = `# Employee Growth: ${data.company_name}\n\n`;

      if (data.current_headcount) {
        text += `**Current Employees:** ${data.current_headcount}\n`;
      }
      if (data.growth_percent !== null && data.growth_percent !== undefined) {
        text += `**Growth:** ${data.growth_percent > 0 ? "+" : ""}${data.growth_percent}%\n`;
      }
      text += `**Data Points:** ${data.snapshots_count}\n\n`;

      if (data.snapshots.length > 0) {
        text += "## History\n\n";
        text += "| Date | Headcount | Source |\n";
        text += "|------|-----------|--------|\n";
        for (const snapshot of data.snapshots) {
          text += `| ${snapshot.recorded_date || "N/A"} | ${snapshot.headcount} | ${snapshot.source || "N/A"} |\n`;
        }
      }

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error fetching headcount history: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: compare_companies
server.tool(
  "compare_companies",
  "Compare multiple companies side-by-side on funding, headcount, and other metrics.",
  {
    slugs: z.array(z.string()).min(2).max(5).describe("Array of company slugs to compare (2-5 companies)"),
  },
  async ({ slugs }) => {
    try {
      const companies = await Promise.all(
        slugs.map(async (slug) => {
          try {
            const response = await client.getCompany(slug);
            return response.data;
          } catch {
            return null;
          }
        })
      );

      const validCompanies = companies.filter((c): c is NonNullable<typeof c> => c !== null);

      if (validCompanies.length === 0) {
        return {
          content: [{
            type: "text",
            text: "No valid companies found for the provided slugs.",
          }],
        };
      }

      let text = `# Company Comparison\n\n`;
      text += `Comparing ${validCompanies.length} companies:\n\n`;

      // Header row
      text += "| Metric |";
      for (const c of validCompanies) {
        text += ` ${c.name} |`;
      }
      text += "\n|--------|";
      for (const _ of validCompanies) {
        text += "---------|";
      }
      text += "\n";

      // Category
      text += "| Category |";
      for (const c of validCompanies) {
        text += ` ${c.category_label || "N/A"} |`;
      }
      text += "\n";

      // Founded
      text += "| Founded |";
      for (const c of validCompanies) {
        text += ` ${c.founded_date || "N/A"} |`;
      }
      text += "\n";

      // Location
      text += "| Location |";
      for (const c of validCompanies) {
        const loc = [c.location.city, c.location.country].filter(Boolean).join(", ");
        text += ` ${loc || "N/A"} |`;
      }
      text += "\n";

      // Employees
      text += "| Employees |";
      for (const c of validCompanies) {
        text += ` ${c.current_headcount?.toLocaleString() || "N/A"} |`;
      }
      text += "\n";

      // Total Funding
      text += "| Total Funding |";
      for (const c of validCompanies) {
        text += ` ${c.total_funding_formatted || "N/A"} |`;
      }
      text += "\n";

      // Rounds
      text += "| Funding Rounds |";
      for (const c of validCompanies) {
        text += ` ${c.funding_rounds_count} |`;
      }
      text += "\n";

      // Latest Round
      text += "| Latest Round |";
      for (const c of validCompanies) {
        if (c.latest_funding) {
          text += ` ${c.latest_funding.round_type || "?"} (${c.latest_funding.amount_formatted || "?"}) |`;
        } else {
          text += " N/A |";
        }
      }
      text += "\n";

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error comparing companies: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: list_categories
server.tool(
  "list_categories",
  "List all available company categories for filtering searches.",
  {},
  async () => {
    try {
      const response = await client.getStats();
      const categories = response.data.categories;

      let text = "# Available Categories\n\n";
      text += "Use these category keys when filtering company searches:\n\n";

      for (const [key, label] of Object.entries(categories)) {
        text += `- **${key}** - ${label}\n`;
      }

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error fetching categories: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Tool: get_stats
server.tool(
  "get_stats",
  "Get overall database statistics - total companies, people, funding tracked, etc.",
  {},
  async () => {
    try {
      const response = await client.getStats();
      const stats = response.data;

      let text = "# StartupGraph Statistics\n\n";
      text += `- **Companies:** ${stats.companies_count}\n`;
      text += `- **People:** ${stats.people_count}\n`;
      text += `- **Funding Rounds:** ${stats.funding_rounds_count}\n`;
      text += `- **Total Funding Tracked:** ${stats.total_funding_formatted}\n\n`;

      text += "## Categories\n\n";
      for (const [key, label] of Object.entries(stats.categories)) {
        text += `- ${label} (\`${key}\`)\n`;
      }

      text += "\n## Countries\n\n";
      text += stats.countries.join(", ");

      return { content: [{ type: "text", text }] };
    } catch (error) {
      return {
        content: [{
          type: "text",
          text: `Error fetching stats: ${error instanceof Error ? error.message : String(error)}`,
        }],
      };
    }
  }
);

// Run the server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error(`StartupGraph MCP Server v1.0.0`);
  console.error(`API URL: ${apiUrl}`);
}

main().catch((error) => {
  console.error("Fatal error:", error);
  process.exit(1);
});
