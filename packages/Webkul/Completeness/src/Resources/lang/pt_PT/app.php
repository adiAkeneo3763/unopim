<?php

return [
    'components' => [
        'layouts' => [
            'sidebar' => [
                'completeness' => 'Integridade',
            ],
        ],
    ],
    'catalog' => [
        'families' => [
            'edit' => [
                'completeness' => [
                    'update-success'      => 'Integridade atualizada com sucesso',
                    'title'               => 'Integridade',
                    'configure'           => 'Configurar integridade',
                    'channel-required'    => 'Necessário nos canais',
                    'save-btn'            => 'Guardar',
                    'back-btn'            => 'Voltar',
                    'mass-update-success' => 'Integridade atualizada com sucesso',
                    'datagrid'            => [
                        'code'             => 'Código',
                        'name'             => 'Nome',
                        'channel-required' => 'Necessário nos canais',
                        'actions'          => [
                            'change-requirement' => 'Alterar requisito de integridade',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'index' => [
                'datagrid' => [
                    'missing-completeness-setting' => 'Nenhuma configuração',
                    'completeness'                 => 'Completo',
                ],
            ],
            'edit' => [
                'completeness' => [
                    'title'    => 'Integridade',
                    'subtitle' => 'Integridade média',
                ],
                'required-attributes' => 'atributos obrigatórios em falta',
            ],
        ],
    ],
    'notifications' => [
        'completeness-title'             => 'Cálculo de completude concluído',
        'completeness-calculated'        => 'Completude calculada para :count produtos.',
        'completeness-calculated-family' => 'Completude calculada para :count produtos na família ":family".',
        'email-subject'                  => 'Cálculo de completude concluído',
        'email-greeting'                 => 'Olá,',
        'email-body'                     => 'O cálculo de completude foi concluído para :count produtos.',
        'email-body-family'              => 'O cálculo de completude foi concluído para :count produtos na família de atributos ":family".',
        'email-footer'                   => 'Pode ver os detalhes de completude no seu painel.',
    ],
    'dashboard' => [
        'index' => [
            'completeness' => [
                'calculated-products' => 'Produtos calculados',
                'suggestion'          => [
                    'low'     => 'Baixa integridade — adicione detalhes para melhorar.',
                    'medium'  => 'Continue, continue a adicionar informações.',
                    'high'    => 'Quase completo, faltam apenas alguns detalhes.',
                    'perfect' => 'As informações do produto estão totalmente completas.',
                ],
            ],
        ],
    ],
];
