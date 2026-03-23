<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Повнота',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Повноту успішно оновлено',
                    'title'               => 'Повнота',
                    'configure'           => 'Налаштувати повноту',
                    'channel-required'    => 'Потрібно в каналах',
                    'save-btn'            => 'Зберегти',
                    'back-btn'            => 'Назад',
                    'mass-update-success' => 'Повноту успішно оновлено',
                    'datagrid'            => [
                        'code'             => 'Код',
                        'name'             => 'Назва',
                        'channel-required' => 'Потрібно в каналах',
                        'actions'          => [
                            'change-requirement' => 'Змінити вимогу повноти',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Немає налаштувань',
                    'completeness'                 => 'Повний',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Повнота',
                    'subtitle' => 'Середня повнота',
                ],
                'required-attributes' => 'відсутні обов’язкові атрибути',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Розрахунок повноти завершено',
        'completeness-calculated'        => 'Повноту розраховано для :count товарів.',
        'completeness-calculated-family' => 'Повноту розраховано для :count товарів у родині ":family".',
        'email-subject'                  => 'Розрахунок повноти завершено',
        'email-greeting'                 => 'Вітаємо,',
        'email-body'                     => 'Розрахунок повноти завершено для :count товарів.',
        'email-body-family'              => 'Розрахунок повноти завершено для :count товарів у родині атрибутів ":family".',
        'email-footer'                   => 'Ви можете переглянути деталі повноти на своїй панелі.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Розраховані продукти',
                'suggestion'          => [
                    'low'     => 'Низька повнота — додайте деталі, щоб покращити.',
                    'medium'  => 'Продовжуйте, продовжуйте додавати інформацію.',
                    'high'    => 'Майже готово, залишилося лише кілька деталей.',
                    'perfect' => 'Інформація про продукт повністю завершена.',
                ],
            ],
        ],
    ],
];
