#!/bin/bash
# Fetch GitHub orgs and output JSON lines
# Usage: bash scripts/fetch_github_orgs.sh > /tmp/github_orgs.jsonl

set -e

OUTPUT="/tmp/github_orgs_raw.jsonl"
> "$OUTPUT"

# Strategy 1: High-follower orgs (most likely companies)
echo "Fetching high-follower orgs..." >&2
for page in $(seq 1 10); do
  echo "  Page $page (followers>100)..." >&2
  gh api "/search/users?q=type:org+repos:>10+followers:>100&per_page=100&page=$page" \
    --jq '.items[] | {login: .login}' >> "$OUTPUT"
  sleep 2
done

# Strategy 2: Orgs with followers>50 (broader net)
echo "Fetching medium-follower orgs..." >&2
for page in $(seq 1 10); do
  echo "  Page $page (followers>50, repos>20)..." >&2
  gh api "/search/users?q=type:org+repos:>20+followers:>50&per_page=100&page=$page" \
    --jq '.items[] | {login: .login}' >> "$OUTPUT"
  sleep 2
done

# Deduplicate logins
echo "Deduplicating..." >&2
sort -u "$OUTPUT" | jq -r '.login' | sort -u > /tmp/github_org_logins.txt
TOTAL=$(wc -l < /tmp/github_org_logins.txt)
echo "Total unique orgs: $TOTAL" >&2

# Now fetch details for each org
DETAILS="/tmp/github_orgs_details.jsonl"
> "$DETAILS"
COUNT=0
while IFS= read -r login; do
  COUNT=$((COUNT + 1))
  echo "  [$COUNT/$TOTAL] Fetching $login..." >&2
  gh api "/orgs/$login" --jq '{
    login: .login,
    name: (.name // .login),
    description: .description,
    website: .blog,
    location: .location,
    html_url: .html_url,
    public_repos: .public_repos,
    followers: .followers,
    created_at: .created_at,
    email: .email,
    twitter: .twitter_username
  }' >> "$DETAILS" 2>/dev/null || echo "  SKIP $login" >&2
  
  # Rate limit: ~5000 requests/hour for core API = ~83/min
  # Be conservative
  if (( COUNT % 50 == 0 )); then
    echo "  Sleeping 30s for rate limit..." >&2
    sleep 30
  else
    sleep 0.5
  fi
done < /tmp/github_org_logins.txt

echo "Done. Details in $DETAILS" >&2
