<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Funding Round Deduplication
    |--------------------------------------------------------------------------
    |
    | Configuration for the funding round deduplication service.
    |
    */
    'dedup' => [
        // Number of days within which rounds are considered potentially duplicate
        'date_tolerance_days' => (int) env('DEDUP_DATE_TOLERANCE_DAYS', 30),

        // Percentage tolerance for amount comparison (0.10 = 10%)
        'amount_tolerance' => (float) env('DEDUP_AMOUNT_TOLERANCE', 0.10),
    ],

];
