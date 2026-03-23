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
                    'completeness'                 => 'Complet',
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
        'completeness-title'             => 'Calcul de complétude terminé',
        'completeness-calculated'        => 'Complétude calculée pour :count produits.',
        'completeness-calculated-family' => 'Complétude calculée pour :count produits dans la famille ":family".',
        'email-subject'                  => 'Calcul de complétude terminé',
        'email-greeting'                 => 'Bonjour,',
        'email-body'                     => 'Le calcul de complétude a été réalisé pour :count produits.',
        'email-body-family'              => 'Le calcul de complétude a été réalisé pour :count produits dans la famille d\'attributs ":family".',
        'email-footer'                   => 'Vous pouvez consulter les détails de complétude sur votre tableau de bord.',
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
