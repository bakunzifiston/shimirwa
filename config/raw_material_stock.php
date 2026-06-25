<?php

return [
    'types' => [
        'Raw Material'      => 'Raw Material',
        'Packaging Material' => 'Packaging Material',
        'Other'             => 'Other',
    ],

    'items_by_type' => [
        'Raw Material' => [
            'Maize'   => 'Maize',
            'Soy'     => 'Soy',
            'Sorghum' => 'Sorghum',
            'Wheat'   => 'Wheat',
        ],
        // Default packaging material items — overridden by ProductCatalog when entries exist
        'Packaging Material' => [
            '1kg bag' => '1kg bag',
            '5kg bag' => '5kg bag',
            'Box'     => 'Box',
            'Sack'    => 'Sack',
        ],
    ],
];
