<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Bütünlük',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Bütünlük başarıyla güncellendi',
                    'title'               => 'Bütünlük',
                    'configure'           => 'Bütünlüğü yapılandır',
                    'channel-required'    => 'Kanallarda gerekli',
                    'save-btn'            => 'Kaydet',
                    'back-btn'            => 'Geri',
                    'mass-update-success' => 'Bütünlük başarıyla güncellendi',
                    'datagrid'            => [
                        'code'             => 'Kod',
                        'name'             => 'İsim',
                        'channel-required' => 'Kanallarda gerekli',
                        'actions'          => [
                            'change-requirement' => 'Bütünlük gereksinimini değiştir',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Ayar yok',
                    'completeness'                 => 'Tam',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Bütünlük',
                    'subtitle' => 'Ortalama bütünlük',
                ],
                'required-attributes' => 'eksik gerekli özellikler',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Tamlık Hesaplama Tamamlandı',
        'completeness-calculated'        => ':count ürün için tamlık hesaplandı.',
        'completeness-calculated-family' => '":family" ailesindeki :count ürün için tamlık hesaplandı.',
        'email-subject'                  => 'Tamlık Hesaplama Tamamlandı',
        'email-greeting'                 => 'Merhaba,',
        'email-body'                     => ':count ürün için tamlık hesaplaması tamamlandı.',
        'email-body-family'              => '":family" öznitelik ailesindeki :count ürün için tamlık hesaplaması tamamlandı.',
        'email-footer'                   => 'Tamlık ayrıntılarını kontrol panelinizde görüntüleyebilirsiniz.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Hesaplanan ürünler',
                'suggestion'          => [
                    'low'     => 'Düşük bütünlük — iyileştirmek için ayrıntılar ekleyin.',
                    'medium'  => 'Devam edin, bilgi eklemeye devam edin.',
                    'high'    => 'Neredeyse tamamlandı, sadece birkaç ayrıntı kaldı.',
                    'perfect' => 'Ürün bilgileri tamamen tamamlandı.',
                ],
            ],
        ],
    ],
];
