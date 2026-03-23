<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => '完整性',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => '完整性已成功更新',
                    'title'               => '完整性',
                    'configure'           => '配置完整性',
                    'channel-required'    => '頻道中必需',
                    'save-btn'            => '儲存',
                    'back-btn'            => '返回',
                    'mass-update-success' => '完整性已成功更新',
                    'datagrid'            => [
                        'code'             => '代碼',
                        'name'             => '名稱',
                        'channel-required' => '頻道中必需',
                        'actions'          => [
                            'change-requirement' => '更改完整性要求',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => '無設定',
                    'completeness'                 => '完整',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => '完整性',
                    'subtitle' => '平均完整性',
                ],
                'required-attributes' => '缺少必填屬性',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => '完整度計算已完成',
        'completeness-calculated'        => '已為 :count 個產品計算完整度。',
        'completeness-calculated-family' => '已為系列「:family」中的 :count 個產品計算完整度。',
        'email-subject'                  => '完整度計算已完成',
        'email-greeting'                 => '您好，',
        'email-body'                     => '已為 :count 個產品完成完整度計算。',
        'email-body-family'              => '已為屬性系列「:family」中的 :count 個產品完成完整度計算。',
        'email-footer'                   => '您可以在儀表板上查看完整度詳情。',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => '已計算的產品',
                'suggestion'          => [
                    'low'     => '完整性較低，請新增詳細資訊以改善。',
                    'medium'  => '繼續，繼續新增資訊。',
                    'high'    => '幾乎完成，只剩一些細節。',
                    'perfect' => '產品資訊已完全完整。',
                ],
            ],
        ],
    ],
];
