<?php

it('should return the magic ai configuration page', function () {
    $this->loginAsAdmin();

    $this->get(route('admin.configuration.edit', ['general', 'magic_ai']))
        ->assertOk()
        ->assertSeeText(trans('admin::app.configuration.index.general.magic-ai.title'))
        ->assertSeeText(trans('admin::app.configuration.index.general.magic-ai.settings.title'));
});

it('should save and update the settings for magic ai', function () {
    $this->loginAsAdmin();

    $data = [];

    $configData = include __DIR__.'/../../../src/Config/system.php';

    $configData = array_filter($configData, function ($item) {
        return ($item['key'] ?? '') == 'general.magic_ai.settings';
    });

    $configData = json_encode(current($configData));

    $fields = [
        'enabled' => '1',
    ];

    $data['general']['magic_ai']['settings'] = $fields;

    foreach ($fields as $field) {
        $data['keys'][] = $configData;
    }

    $response = $this->post(route('admin.configuration.store'), [
        ...$data,
    ])->assertRedirect();

    $response->assertSessionHas('success', trans('admin::app.configuration.index.save-message'));

    $this->assertDatabaseHas('core_config', [
        'code'  => 'general.magic_ai.settings.enabled',
        'value' => '1',
    ]);
});

it('should save and update the translation settings for magic ai', function () {
    $this->loginAsAdmin();

    $data = [];

    $configData = include __DIR__.'/../../../src/Config/system.php';

    $configData = array_filter($configData, function ($item) {
        return ($item['key'] ?? '') == 'general.magic_ai.translation';
    });

    $configData = json_encode(current($configData));

    $fields = [
        'enabled'        => '1',
        'source_channel' => 'default',
        'source_locale'  => 'af_ZA',
        'ai_platform'    => '0',
        'ai_model'       => '0',
    ];

    $data['general']['magic_ai']['translation'] = $fields;

    foreach ($fields as $field) {
        $data['keys'][] = $configData;
    }

    $response = $this->post(route('admin.configuration.store'), [
        ...$data,
    ])->assertRedirect();

    $response->assertSessionHas('success', trans('admin::app.configuration.index.save-message'));

    $records = [
        ['code' => 'general.magic_ai.translation.enabled', 'value' => '1'],
        ['code' => 'general.magic_ai.translation.source_channel', 'value' => 'default'],
        ['code' => 'general.magic_ai.translation.source_locale', 'value' => 'af_ZA'],
    ];

    foreach ($records as $record) {
        $this->assertDatabaseHas('core_config', $record);
    }
});
