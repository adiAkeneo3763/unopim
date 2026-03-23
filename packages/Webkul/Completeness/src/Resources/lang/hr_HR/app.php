<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Potpunost',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Potpunost je uspješno ažurirana',
                    'title'               => 'Potpunost',
                    'configure'           => 'Konfiguriraj potpunost',
                    'channel-required'    => 'Obavezno u kanalima',
                    'save-btn'            => 'Spremi',
                    'back-btn'            => 'Natrag',
                    'mass-update-success' => 'Potpunost je uspješno ažurirana',
                    'datagrid'            => [
                        'code'             => 'Šifra',
                        'name'             => 'Naziv',
                        'channel-required' => 'Obavezno u kanalima',
                        'actions'          => [
                            'change-requirement' => 'Promijeni zahtjev za potpunost',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Nema postavke',
                    'completeness'                 => 'Potpun',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Potpunost',
                    'subtitle' => 'Prosječna potpunost',
                ],
                'required-attributes' => 'Nedostaju obavezni atributi',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Izračun potpunosti završen',
        'completeness-calculated'        => 'Potpunost izračunata za :count proizvoda.',
        'completeness-calculated-family' => 'Potpunost izračunata za :count proizvoda u obitelji ":family".',
        'email-subject'                  => 'Izračun potpunosti završen',
        'email-greeting'                 => 'Pozdrav,',
        'email-body'                     => 'Izračun potpunosti je završen za :count proizvoda.',
        'email-body-family'              => 'Izračun potpunosti je završen za :count proizvoda u obitelji atributa ":family".',
        'email-footer'                   => 'Detalje potpunosti možete pregledati na nadzornoj ploči.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Izračunati proizvodi',
                'suggestion'          => [
                    'low'     => 'Niska potpunost — dodajte detalje kako biste poboljšali informacije.',
                    'medium'  => 'Nastavite, dodajte dodatne informacije.',
                    'high'    => 'Gotovo kompletno — ostalo je samo nekoliko detalja.',
                    'perfect' => 'Informacije o proizvodu su potpuno dovršene.',
                ],
            ],
        ],
    ],
];
