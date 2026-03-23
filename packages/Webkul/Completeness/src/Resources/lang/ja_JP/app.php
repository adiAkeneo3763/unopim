<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => '完全性',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => '完全性が正常に更新されました',
                    'title'               => '完全性',
                    'configure'           => '完全性を設定',
                    'channel-required'    => 'チャンネルで必須',
                    'save-btn'            => '保存',
                    'back-btn'            => '戻る',
                    'mass-update-success' => '完全性が正常に更新されました',
                    'datagrid'            => [
                        'code'             => 'コード',
                        'name'             => '名前',
                        'channel-required' => 'チャンネルで必須',
                        'actions'          => [
                            'change-requirement' => '完全性要件を変更',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => '設定なし',
                    'completeness'                 => '完全',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => '完全性',
                    'subtitle' => '平均完全性',
                ],
                'required-attributes' => '必須属性が欠落しています',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => '完全性の計算が完了しました',
        'completeness-calculated'        => ':count 件の製品の完全性が計算されました。',
        'completeness-calculated-family' => 'ファミリー「:family」の :count 件の製品の完全性が計算されました。',
        'email-subject'                  => '完全性の計算が完了しました',
        'email-greeting'                 => 'こんにちは、',
        'email-body'                     => ':count 件の製品の完全性計算が完了しました。',
        'email-body-family'              => '属性ファミリー「:family」の :count 件の製品の完全性計算が完了しました。',
        'email-footer'                   => 'ダッシュボードで完全性の詳細を確認できます。',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => '計算された製品',
                'suggestion'          => [
                    'low'     => '完全性が低いです。改善するには詳細を追加してください。',
                    'medium'  => '続けてください。情報を追加し続けてください。',
                    'high'    => 'ほぼ完了です。残りは数件の詳細のみです。',
                    'perfect' => '製品情報は完全に完成しています。',
                ],
            ],
        ],
    ],
];
