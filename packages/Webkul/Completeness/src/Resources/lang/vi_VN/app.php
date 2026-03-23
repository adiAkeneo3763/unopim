<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Toàn vẹn',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Toàn vẹn được cập nhật thành công',
                    'title'               => 'Toàn vẹn',
                    'configure'           => 'Cấu hình toàn vẹn',
                    'channel-required'    => 'Yêu cầu trong các kênh',
                    'save-btn'            => 'Lưu',
                    'back-btn'            => 'Quay lại',
                    'mass-update-success' => 'Toàn vẹn được cập nhật thành công',
                    'datagrid'            => [
                        'code'             => 'Mã',
                        'name'             => 'Tên',
                        'channel-required' => 'Yêu cầu trong các kênh',
                        'actions'          => [
                            'change-requirement' => 'Thay đổi yêu cầu toàn vẹn',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Không có cài đặt',
                    'completeness'                 => 'Đầy đủ',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Toàn vẹn',
                    'subtitle' => 'Toàn vẹn trung bình',
                ],
                'required-attributes' => 'thiếu thuộc tính bắt buộc',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Tính toán mức hoàn thiện đã hoàn tất',
        'completeness-calculated'        => 'Đã tính mức hoàn thiện cho :count sản phẩm.',
        'completeness-calculated-family' => 'Đã tính mức hoàn thiện cho :count sản phẩm trong nhóm ":family".',
        'email-subject'                  => 'Tính toán mức hoàn thiện đã hoàn tất',
        'email-greeting'                 => 'Xin chào,',
        'email-body'                     => 'Việc tính toán mức hoàn thiện đã hoàn tất cho :count sản phẩm.',
        'email-body-family'              => 'Việc tính toán mức hoàn thiện đã hoàn tất cho :count sản phẩm trong nhóm thuộc tính ":family".',
        'email-footer'                   => 'Bạn có thể xem chi tiết mức hoàn thiện trên bảng điều khiển.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Sản phẩm đã tính',
                'suggestion'          => [
                    'low'     => 'Độ toàn vẹn thấp — thêm chi tiết để cải thiện.',
                    'medium'  => 'Tiếp tục, tiếp tục thêm thông tin.',
                    'high'    => 'Gần hoàn thành, chỉ còn vài chi tiết.',
                    'perfect' => 'Thông tin sản phẩm đã hoàn toàn đầy đủ.',
                ],
            ],
        ],
    ],
];
