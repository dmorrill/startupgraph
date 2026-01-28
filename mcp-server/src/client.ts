import type {
  ApiResponse,
  Company,
  CompanySummary,
  FundingResponse,
  HeadcountResponse,
  PaginatedResponse,
  PeopleResponse,
  Person,
  SearchResponse,
  StatsResponse,
} from "./types.js";

export class StartupGraphClient {
  private baseUrl: string;

  constructor(baseUrl: string = "http://localhost:8000/api") {
    this.baseUrl = baseUrl.replace(/\/$/, "");
  }

  private async fetch<T>(path: string): Promise<T> {
    const url = `${this.baseUrl}${path}`;
    const response = await fetch(url, {
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      throw new Error(`API request failed: ${response.status} ${response.statusText}`);
    }

    return response.json() as Promise<T>;
  }

  async getStats(): Promise<ApiResponse<StatsResponse>> {
    return this.fetch("/stats");
  }

  async searchCompanies(params: {
    q?: string;
    category?: string;
    country?: string;
    limit?: number;
  }): Promise<ApiResponse<SearchResponse>> {
    const searchParams = new URLSearchParams();
    if (params.q) searchParams.set("q", params.q);
    if (params.category) searchParams.set("category", params.category);
    if (params.country) searchParams.set("country", params.country);
    if (params.limit) searchParams.set("limit", params.limit.toString());

    return this.fetch(`/search?${searchParams.toString()}`);
  }

  async listCompanies(params: {
    q?: string;
    category?: string;
    country?: string;
    sort?: string;
    order?: "asc" | "desc";
    per_page?: number;
    page?: number;
  } = {}): Promise<PaginatedResponse<CompanySummary>> {
    const searchParams = new URLSearchParams();
    if (params.q) searchParams.set("q", params.q);
    if (params.category) searchParams.set("category", params.category);
    if (params.country) searchParams.set("country", params.country);
    if (params.sort) searchParams.set("sort", params.sort);
    if (params.order) searchParams.set("order", params.order);
    if (params.per_page) searchParams.set("per_page", params.per_page.toString());
    if (params.page) searchParams.set("page", params.page.toString());

    return this.fetch(`/companies?${searchParams.toString()}`);
  }

  async getCompany(slug: string): Promise<ApiResponse<Company>> {
    return this.fetch(`/companies/${encodeURIComponent(slug)}`);
  }

  async getCompanyFunding(slug: string): Promise<ApiResponse<FundingResponse>> {
    return this.fetch(`/companies/${encodeURIComponent(slug)}/funding`);
  }

  async getCompanyPeople(slug: string): Promise<ApiResponse<PeopleResponse>> {
    return this.fetch(`/companies/${encodeURIComponent(slug)}/people`);
  }

  async getCompanyHeadcount(slug: string): Promise<ApiResponse<HeadcountResponse>> {
    return this.fetch(`/companies/${encodeURIComponent(slug)}/headcount`);
  }

  async getPerson(slug: string): Promise<ApiResponse<Person>> {
    return this.fetch(`/people/${encodeURIComponent(slug)}`);
  }
}
