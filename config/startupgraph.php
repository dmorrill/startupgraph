<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Funding Round Deduplication
    |--------------------------------------------------------------------------
    */

    'deduplication' => [
        'date_tolerance_days' => (int) env('DEDUP_DATE_TOLERANCE_DAYS', 30),
        'amount_tolerance_percent' => (float) env('DEDUP_AMOUNT_TOLERANCE_PERCENT', 0.10),
    ],

];
