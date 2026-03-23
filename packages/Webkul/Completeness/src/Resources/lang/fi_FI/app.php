<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Completeness',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Compeleteness updated successfully',
                    'title'               => 'Completeness',
                    'configure'           => 'Configure Completeness',
                    'channel-required'    => 'Required in Channels',
                    'save-btn'            => 'Save',
                    'back-btn'            => 'Back',
                    'mass-update-success' => 'Compeleteness updated successfully',
                    'datagrid'            => [
                        'code'             => 'Code',
                        'name'             => 'Name',
                        'channel-required' => 'Required in Channels',
                        'actions'          => [
                            'change-requirement' => 'Change Completeness Requirement',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'N/A',
                    'completeness'                 => 'Täydellinen',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Completeness',
                    'subtitle' => 'Average completeness',
                ],
                'required-attributes' => 'missing required attributes',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Täydellisyyslaskenta suoritettu',
        'completeness-calculated'        => 'Täydellisyys laskettu :count tuotteelle.',
        'completeness-calculated-family' => 'Täydellisyys laskettu :count tuotteelle perheessä ":family".',
        'email-subject'                  => 'Täydellisyyslaskenta suoritettu',
        'email-greeting'                 => 'Hei,',
        'email-body'                     => 'Täydellisyyslaskenta on suoritettu :count tuotteelle.',
        'email-body-family'              => 'Täydellisyyslaskenta on suoritettu :count tuotteelle attribuuttiperheessä ":family".',
        'email-footer'                   => 'Voit tarkastella täydellisyystietoja hallintapaneelissasi.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Calculated products',
                'suggestion'          => [
                    'low'     => 'Low completeness, add details to improve.',
                    'medium'  => 'Keep going, continue adding information.',
                    'high'    => 'Almost complete, just a few details left.',
                    'perfect' => 'Product information is fully complete.',
                ],
            ],
        ],
    ],
];
