<?php

namespace App\Contracts;

interface CompanyDiscoverySource
{
    /**
     * Get the source identifier (e.g., 'techcrunch', 'yc').
     */
    public function name(): string;

    /**
     * Discover companies from this source.
     *
     * @param int $days How many days back to look
     * @return array<int, array{name: string, description?: string, website?: string, funding_amount?: float, funding_round?: string, source_url?: string, batch?: string}>
     */
    public function discover(int $days = 7): array;
}
