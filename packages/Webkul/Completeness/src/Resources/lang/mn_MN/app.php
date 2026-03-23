<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Бүрэн байдал',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Бүрэн байдал амжилттай шинэчлэгдлээ',
                    'title'               => 'Бүрэн байдал',
                    'configure'           => 'Бүрэн байдлыг тохируулах',
                    'channel-required'    => 'Сувагт шаардлагатай',
                    'save-btn'            => 'Хадгалах',
                    'back-btn'            => 'Буцах',
                    'mass-update-success' => 'Бүрэн байдал амжилттай шинэчлэгдлээ',
                    'datagrid'            => [
                        'code'             => 'Код',
                        'name'             => 'Нэр',
                        'channel-required' => 'Сувагт шаардлагатай',
                        'actions'          => [
                            'change-requirement' => 'Бүрэн байдлын шаардлагыг өөрчлөх',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Тохиргоо байхгүй байна',
                    'completeness'                 => 'Бүрэн',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Бүрэн байдал',
                    'subtitle' => 'Дундаж бүрэн байдал',
                ],
                'required-attributes' => 'дахин харах шаардлагатай заавал шаардлагатай шинж чанарууд',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Бүрэн байдлын тооцоо дууссан',
        'completeness-calculated'        => ':count бүтээгдэхүүний бүрэн байдал тооцоолсон.',
        'completeness-calculated-family' => '":family" гэр бүлийн :count бүтээгдэхүүний бүрэн байдал тооцоолсон.',
        'email-subject'                  => 'Бүрэн байдлын тооцоо дууссан',
        'email-greeting'                 => 'Сайн байна уу,',
        'email-body'                     => ':count бүтээгдэхүүний бүрэн байдлын тооцоо дууссан.',
        'email-body-family'              => '":family" шинж чанарын гэр бүлийн :count бүтээгдэхүүний бүрэн байдлын тооцоо дууссан.',
        'email-footer'                   => 'Та хянах самбар дээрх бүрэн байдлын дэлгэрэнгүйг харах боломжтой.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Тооцоолсон бүтээгдэхүүнүүд',
                'suggestion'          => [
                    'low'     => 'Бүрэн байдал нь бага байна — сайжруулахын тулд дэлгэрэнгүй мэдээлэл оруулна уу.',
                    'medium'  => 'Үргэлжлүүлэн мэдээлэл оруулсаар байгаарай.',
                    'high'    => 'Бараг төгс — цөөн хэдэн дэлгэрэнгүй мэдээлэл үлджээ.',
                    'perfect' => 'Бүтээгдэхүүний мэдээлэл бүрэн бөгөөд дууссан байна.',
                ],
            ],
        ],
    ],
];
