<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Completeness',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Compeleteness updated successfully',
                    'title'               => 'Completeness',
                    'configure'           => 'Configure Completeness',
                    'channel-required'    => 'Required in Channels',
                    'save-btn'            => 'Save',
                    'back-btn'            => 'Back',
                    'mass-update-success' => 'Compeleteness updated successfully',
                    'datagrid'            => [
                        'code'             => 'Code',
                        'name'             => 'Name',
                        'channel-required' => 'Required in Channels',
                        'actions'          => [
                            'change-requirement' => 'Change Completeness Requirement',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'N/A',
                    'completeness'                 => 'पूर्ण',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Completeness',
                    'subtitle' => 'Average completeness',
                ],
                'required-attributes' => 'missing required attributes',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'पूर्णता गणना पूर्ण',
        'completeness-calculated'        => ':count उत्पादों के लिए पूर्णता की गणना की गई।',
        'completeness-calculated-family' => '":family" परिवार में :count उत्पादों के लिए पूर्णता की गणना की गई।',
        'email-subject'                  => 'पूर्णता गणना पूर्ण',
        'email-greeting'                 => 'नमस्कार,',
        'email-body'                     => ':count उत्पादों के लिए पूर्णता गणना पूर्ण हो गई है।',
        'email-body-family'              => '":family" विशेषता परिवार में :count उत्पादों के लिए पूर्णता गणना पूर्ण हो गई है।',
        'email-footer'                   => 'आप अपने डैशबोर्ड पर पूर्णता विवरण देख सकते हैं।',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Calculated products',
                'suggestion'          => [
                    'low'     => 'Low completeness, add details to improve.',
                    'medium'  => 'Keep going, continue adding information.',
                    'high'    => 'Almost complete, just a few details left.',
                    'perfect' => 'Product information is fully complete.',
                ],
            ],
        ],
    ],
];
