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
                    'completeness'                 => 'Vollständig',
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
        'completeness-title'             => 'Vollständigkeitsberechnung abgeschlossen',
        'completeness-calculated'        => 'Vollständigkeit für :count Produkte berechnet.',
        'completeness-calculated-family' => 'Vollständigkeit für :count Produkte in der Familie ":family" berechnet.',
        'email-subject'                  => 'Vollständigkeitsberechnung abgeschlossen',
        'email-greeting'                 => 'Hallo,',
        'email-body'                     => 'Die Vollständigkeitsberechnung wurde für :count Produkte abgeschlossen.',
        'email-body-family'              => 'Die Vollständigkeitsberechnung wurde für :count Produkte in der Attributfamilie ":family" abgeschlossen.',
        'email-footer'                   => 'Sie können die Vollständigkeitsdetails auf Ihrem Dashboard einsehen.',
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
