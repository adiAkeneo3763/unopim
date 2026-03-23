<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Kumpletong',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Matagumpay na na-update ang kumpletong',
                    'title'               => 'Kumpletong',
                    'configure'           => 'I-configure ang Kumpletong',
                    'channel-required'    => 'Kinakailangan sa mga channel',
                    'save-btn'            => 'I-save',
                    'back-btn'            => 'Bumalik',
                    'mass-update-success' => 'Matagumpay na na-update ang kumpletong',
                    'datagrid'            => [
                        'code'             => 'Code',
                        'name'             => 'Pangalan',
                        'channel-required' => 'Kinakailangan sa mga channel',
                        'actions'          => [
                            'change-requirement' => 'Baguhin ang Kahilingan sa Kumpletong',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Walang setting',
                    'completeness'                 => 'Kumpleto',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Kumpletong',
                    'subtitle' => 'Average completeness',
                ],
                'required-attributes' => 'mga nawawalang kinakailangang attributes',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Natapos ang Pagkalkula ng Pagkakumpleto',
        'completeness-calculated'        => 'Nakalkula ang pagkakumpleto para sa :count produkto.',
        'completeness-calculated-family' => 'Nakalkula ang pagkakumpleto para sa :count produkto sa pamilya ":family".',
        'email-subject'                  => 'Natapos ang Pagkalkula ng Pagkakumpleto',
        'email-greeting'                 => 'Kumusta,',
        'email-body'                     => 'Natapos na ang pagkalkula ng pagkakumpleto para sa :count produkto.',
        'email-body-family'              => 'Natapos na ang pagkalkula ng pagkakumpleto para sa :count produkto sa attribute family ":family".',
        'email-footer'                   => 'Maaari mong tingnan ang mga detalye ng pagkakumpleto sa iyong dashboard.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Mga na-compute na produkto',
                'suggestion'          => [
                    'low'     => 'Mababang kumpletong — magdagdag ng detalye para mapabuti.',
                    'medium'  => 'Magpatuloy, magpatuloy sa pagdaragdag ng impormasyon.',
                    'high'    => 'Halos kumpleto, iilang detalye na lang ang natitira.',
                    'perfect' => 'Ang impormasyon ng produkto ay ganap na kumpleto.',
                ],
            ],
        ],
    ],
];
