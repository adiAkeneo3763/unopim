<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Pełność',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Pełność zaktualizowana pomyślnie',
                    'title'               => 'Pełność',
                    'configure'           => 'Skonfiguruj pełność',
                    'channel-required'    => 'Wymagane w kanałach',
                    'save-btn'            => 'Zapisz',
                    'back-btn'            => 'Wróć',
                    'mass-update-success' => 'Pełność zaktualizowana pomyślnie',
                    'datagrid'            => [
                        'code'             => 'Kod',
                        'name'             => 'Nazwa',
                        'channel-required' => 'Wymagane w kanałach',
                        'actions'          => [
                            'change-requirement' => 'Zmień wymaganie pełności',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Brak ustawienia',
                    'completeness'                 => 'Kompletny',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Pełność',
                    'subtitle' => 'Średnia pełność',
                ],
                'required-attributes' => 'brakujące wymagane atrybuty',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Obliczanie kompletności zakończone',
        'completeness-calculated'        => 'Kompletność obliczona dla :count produktów.',
        'completeness-calculated-family' => 'Kompletność obliczona dla :count produktów w rodzinie ":family".',
        'email-subject'                  => 'Obliczanie kompletności zakończone',
        'email-greeting'                 => 'Witaj,',
        'email-body'                     => 'Obliczanie kompletności zostało zakończone dla :count produktów.',
        'email-body-family'              => 'Obliczanie kompletności zostało zakończone dla :count produktów w rodzinie atrybutów ":family".',
        'email-footer'                   => 'Szczegóły kompletności możesz zobaczyć na swoim pulpicie.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Obliczone produkty',
                'suggestion'          => [
                    'low'     => 'Niska pełność — dodaj szczegóły, aby poprawić.',
                    'medium'  => 'Kontynuuj, dodawaj dalej informacje.',
                    'high'    => 'Prawie kompletne, pozostało tylko kilka szczegółów.',
                    'perfect' => 'Informacje o produkcie są w pełni kompletne.',
                ],
            ],
        ],
    ],
];
