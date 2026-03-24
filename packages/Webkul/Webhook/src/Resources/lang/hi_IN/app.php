<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'menu' => [
                    'webhook' => [
                        'name' => 'Webhooks',
                    ],
                ],
            ],
        ],
    ],
    'webhook-action' => [
        'delete-failed' => 'कृपया सेटिंग्स से Webhook सक्षम करें',
        'success'       => 'उत्पाद डेटा Webhook पर सफलतापूर्वक भेजा गया',
    ],
    'acl' => [
        'webhook' => [
            'index' => 'Webhook',
        ],
        'settings' => [
            'index'  => 'सेटिंग्स',
            'update' => 'सेटिंग्स अपडेट करें',
        ],
        'logs' => [
            'index'       => 'लॉग',
            'delete'      => 'हटाएं',
            'mass-delete' => 'सामूहिक हटाएं',
        ],
    ],

    'configuration' => [
        'webhook' => [
            'settings' => [
                'index' => [
                    'name'    => 'सेटिंग्स',
                    'title'   => 'Webhook सेटिंग्स',
                    'save'    => 'सहेजें',
                    'general' => 'सामान्य',
                    'active'  => [
                        'label' => 'सक्रिय Webhook',
                    ],
                    'webhook_url' => [
                        'label' => 'Webhook URL',
                    ],
                    'success'    => 'Webhook सेटिंग्स सफलतापूर्वक सहेजी गईं',
                    'title'      => 'Webhook सेटिंग्स',
                    'logs-title' => 'लॉग',
                ],
            ],

            'logs' => [
                'index' => [
                    'datagrid' => [
                        'id'         => 'ID',
                        'sku'        => 'SKU',
                        'created_at' => 'दिनांक/समय',
                        'user'       => 'उपयोगकर्ता',
                        'status'     => 'स्थिति',
                        'success'    => 'सफल',
                        'failed'     => 'विफल',
                        'delete'     => 'हटाएं',
                    ],
                    'title'          => 'Webhook लॉग',
                    'delete-success' => 'Webhook लॉग सफलतापूर्वक हटाए गए',
                    'delete-failed'  => 'Webhook लॉग हटाना अप्रत्याशित रूप से विफल हुआ',
                ],
            ],
        ],
    ],
];
