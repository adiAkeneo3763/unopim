<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'الاكتِمال',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'تم تحديث الاكتِمال بنجاح',
                    'title'               => 'الاكتِمال',
                    'configure'           => 'تكوين الاكتِمال',
                    'channel-required'    => 'مطلوب في القنوات',
                    'save-btn'            => 'حفظ',
                    'back-btn'            => 'رجوع',
                    'mass-update-success' => 'تم تحديث الاكتِمال بنجاح',
                    'datagrid'            => [
                        'code'             => 'الرمز',
                        'name'             => 'الاسم',
                        'channel-required' => 'مطلوب في القنوات',
                        'actions'          => [
                            'change-requirement' => 'تغيير شرط الاكتِمال',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'لا توجد إعدادات',
                    'completeness'                 => 'مكتمل',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'الاكتِمال',
                    'subtitle' => 'متوسط الاكتِمال',
                ],
                'required-attributes' => 'السمات المطلوبة مفقودة',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'اكتمل حساب الاكتمال',
        'completeness-calculated'        => 'تم حساب الاكتمال لـ :count منتج.',
        'completeness-calculated-family' => 'تم حساب الاكتمال لـ :count منتج في العائلة ":family".',
        'email-subject'                  => 'اكتمل حساب الاكتمال',
        'email-greeting'                 => 'مرحبًا،',
        'email-body'                     => 'تم إتمام حساب الاكتمال لـ :count منتج.',
        'email-body-family'              => 'تم إتمام حساب الاكتمال لـ :count منتج في عائلة السمات ":family".',
        'email-footer'                   => 'يمكنك عرض تفاصيل الاكتمال على لوحة التحكم.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'المنتجات المحتسبة',
                'suggestion'          => [
                    'low'     => 'الاكتِمال منخفض، أضف تفاصيل لتحسينه.',
                    'medium'  => 'تابع، استمر في إضافة المعلومات.',
                    'high'    => 'قريب من الاكتمال، تبقّى بعض التفاصيل.',
                    'perfect' => 'معلومات المنتج كاملة تمامًا.',
                ],
            ],
        ],
    ],
];
