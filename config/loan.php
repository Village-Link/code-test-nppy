<?php

return [
    'interest_rate' => 10,
    'amount' => [
        'min' => 100000,
        'max' => 10000000,
    ],
    'term_months' => [
        'min' => 3,
        'max' => 24,
    ],
    'statuses' => [
        'pending',
        'approved',
        'rejected',
        'cancelled',
        'disbursed',
        'closed',
    ],
];
