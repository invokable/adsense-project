<?php

return [
    'access_token' => env('ADS_ACCESS_TOKEN'),
    'refresh_token' => env('ADS_REFRESH_TOKEN'),

    'metrics' => [
        'PAGE_VIEWS',
//        'CLICKS',
//        'COST_PER_CLICK',
        'ESTIMATED_EARNINGS',
        'IMPRESSIONS',
        'ACTIVE_VIEW_VIEWABILITY',
    ],
];
