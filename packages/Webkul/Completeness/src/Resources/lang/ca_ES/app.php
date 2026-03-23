<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Integritat',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Integritat actualitzada correctament',
                    'title'               => 'Integritat',
                    'configure'           => 'Configura la integritat',
                    'channel-required'    => 'Requerit en canals',
                    'save-btn'            => 'Desa',
                    'back-btn'            => 'Enrere',
                    'mass-update-success' => 'Integritat actualitzada correctament',
                    'datagrid'            => [
                        'code'             => 'Codi',
                        'name'             => 'Nom',
                        'channel-required' => 'Requerit en canals',
                        'actions'          => [
                            'change-requirement' => 'Canvia el requisit d\'integritat',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Sense configuració',
                    'completeness'                 => 'Completitud',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Integritat',
                    'subtitle' => 'Mitjana de completitud',
                ],
                'required-attributes' => 'Falten els atributs obligatoris',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Càlcul de completitud finalitzat',
        'completeness-calculated'        => 'Completitud calculada per a :count productes.',
        'completeness-calculated-family' => 'Completitud calculada per a :count productes de la família ":family".',
        'email-subject'                  => 'Càlcul de completitud finalitzat',
        'email-greeting'                 => 'Hola,',
        'email-body'                     => 'El càlcul de completitud s\'ha finalitzat per a :count productes.',
        'email-body-family'              => 'El càlcul de completitud s\'ha finalitzat per a :count productes de la família d\'atributs ":family".',
        'email-footer'                   => 'Podeu veure els detalls de completitud al tauler.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Productes calculats',
                'suggestion'          => [
                    'low'     => 'Integritat baixa, afegeix més detalls per millorar.',
                    'medium'  => 'Segueix endavant, continua afegint informació.',
                    'high'    => 'Quasi complet, només falten unes quantes dades.',
                    'perfect' => 'La informació del producte està completa.',
                ],
            ],
        ],
    ],
];
