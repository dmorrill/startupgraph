export interface Company {
  slug: string;
  name: string;
  website?: string;
  description?: string;
  category?: string;
  category_label?: string;
  founded_date?: string;
  location: {
    city?: string;
    state?: string;
    country?: string;
  };
  linkedin_url?: string;
  current_headcount?: number;
  product_highlights?: string[];
  total_funding?: number;
  total_funding_formatted?: string;
  funding_rounds_count: number;
  latest_funding?: {
    round_type: string;
    amount?: number;
    amount_formatted?: string;
    announced_date?: string;
  };
  people_count: number;
  funding_rounds?: FundingRound[];
  people?: PersonSummary[];
  headcount_snapshots?: HeadcountSnapshot[];
  profile_refreshed_at?: string;
}

export interface CompanySummary {
  slug: string;
  name: string;
  category?: string;
  category_label?: string;
  location: {
    city?: string;
    country?: string;
  };
  current_headcount?: number;
  total_funding?: number;
  total_funding_formatted?: string;
  latest_funding_date?: string;
  funding_rounds_count: number;
}

export interface FundingRound {
  round_type?: string;
  amount?: number;
  amount_formatted?: string;
  currency: string;
  announced_date?: string;
  pre_money_valuation?: number;
  source_url?: string;
  investors?: Investor[];
}

export interface Investor {
  name: string;
  slug: string;
  type?: string;
  is_lead: boolean;
}

export interface Person {
  slug: string;
  name: string;
  bio?: string;
  linkedin_url?: string;
  twitter_url?: string;
  photo_url?: string;
  companies?: CompanyRole[];
}

export interface PersonSummary {
  slug: string;
  name: string;
  role?: string;
  is_current?: boolean;
  linkedin_url?: string;
}

export interface CompanyRole {
  slug: string;
  name: string;
  role?: string;
  is_current: boolean;
  started_at?: string;
  ended_at?: string;
}

export interface HeadcountSnapshot {
  headcount: number;
  recorded_date?: string;
  source?: string;
}

export interface ApiResponse<T> {
  data: T;
  meta: {
    source: string;
    version: string;
    generated_at: string;
  };
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  pagination: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

export interface SearchResponse {
  companies: CompanySummary[];
  people: {
    slug: string;
    name: string;
    current_company?: string;
    current_role?: string;
    linkedin_url?: string;
  }[];
}

export interface StatsResponse {
  companies_count: number;
  people_count: number;
  funding_rounds_count: number;
  total_funding_tracked: number;
  total_funding_formatted: string;
  categories: Record<string, string>;
  countries: string[];
}

export interface FundingResponse {
  company_slug: string;
  company_name: string;
  total_funding: number;
  total_funding_formatted: string;
  rounds_count: number;
  funding_rounds: FundingRound[];
}

export interface PeopleResponse {
  company_slug: string;
  company_name: string;
  total_count: number;
  current_count: number;
  current: PersonSummary[];
  former: PersonSummary[];
}

export interface HeadcountResponse {
  company_slug: string;
  company_name: string;
  current_headcount?: number;
  snapshots_count: number;
  growth_percent?: number;
  snapshots: HeadcountSnapshot[];
}
