<?php

return [
    /**
     * General.
     */
    [
        'key'  => 'general',
        'name' => 'admin::app.configuration.index.general.title',
        'info' => 'admin::app.configuration.index.general.info',
        'sort' => 1,
    ], [
        'key'       => 'general.magic_ai',
        'name'      => 'admin::app.configuration.index.general.magic-ai.title',
        'info'      => 'admin::app.configuration.index.general.magic-ai.info',
        'icon'      => 'settings/magic-ai.svg',
        'sort'      => 3,
    ], [
        'key'    => 'general.magic_ai.settings',
        'name'   => 'admin::app.configuration.index.general.magic-ai.settings.title',
        'info'   => 'admin::app.configuration.index.general.magic-ai.settings.title-info',
        'sort'   => 1,
        'fields' => [
            [
                'name'  => 'enabled',
                'title' => 'admin::app.configuration.index.general.magic-ai.settings.enabled',
                'type'  => 'boolean',
            ], [
                'name'  => 'ai_platform',
                'title' => 'admin::app.configuration.index.general.magic-ai.settings.ai-platform',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.text-generation-platform',
            ], [
                'name'  => 'ai_model',
                'title' => 'admin::app.configuration.index.general.magic-ai.settings.ai-model',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.text-generation-model',
            ],
        ],
    ], [
        'key'    => 'general.magic_ai.image_generation',
        'name'   => 'admin::app.configuration.index.general.magic-ai.image-generation.title',
        'info'   => 'admin::app.configuration.index.general.magic-ai.image-generation.title-info',
        'sort'   => 2,
        'fields' => [
            [
                'name'  => 'enabled',
                'title' => 'admin::app.configuration.index.general.magic-ai.image-generation.enabled',
                'type'  => 'boolean',
            ], [
                'name'  => 'ai_platform',
                'title' => 'admin::app.configuration.index.general.magic-ai.image-generation.ai-platform',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.image-generation-platform',
            ], [
                'name'  => 'ai_model',
                'title' => 'admin::app.configuration.index.general.magic-ai.image-generation.ai-model',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.image-generation-model',
            ],
        ],
    ],
    [
        'key'    => 'general.magic_ai.translation',
        'name'   => 'admin::app.configuration.index.general.magic-ai.translation.title',
        'info'   => 'admin::app.configuration.index.general.magic-ai.translation.title-info',
        'sort'   => 3,
        'fields' => [
            [
                'name'  => 'enabled',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.enabled',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.translation-boolean',
            ], [
                'name'  => 'ai_platform',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.ai-platform',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.translation-platform',
            ], [
                'name'  => 'ai_model',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.translation-model',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.translation-model',
            ], [
                'name'  => 'replace',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.replace-existing-value',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.replace-toggle',
            ], [
                'name'  => 'source_channel',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.global-source-channel',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.channel',
            ], [
                'name'  => 'target_channel',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.target-channel',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.target-channel',
            ], [
                'name'  => 'source_locale',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.global-source-locale',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.locale',
            ], [
                'name'  => 'target_locale',
                'title' => 'admin::app.configuration.index.general.magic-ai.translation.target-locales',
                'type'  => 'blade',
                'path'  => 'admin::configuration.magic-ai.field.target-locale',
            ],
        ],
    ],
];
