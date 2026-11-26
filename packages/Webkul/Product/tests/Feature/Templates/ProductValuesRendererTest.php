<?php

use Webkul\Product\Contracts\ProductValuesRenderer;
use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;

uses(InteractsWithTemplates::class);

beforeEach(function () {
    $this->onlineChannel = $this->templateChannel(['EUR'], ['en_US', 'de_DE']);
    $this->storeChannel = $this->templateChannel(['EUR'], ['de_DE']);
    $this->defaultChannel = core()->getDefaultChannelCode();
    $this->defaultLocale = core()->getDefaultLocaleCodeFromDefaultChannel();

    $this->variableAttribute('rv_name', 'text', ['value_per_locale' => 1]);
    $this->variableAttribute('rv_tagline', 'text', ['value_per_channel' => 1]);
    $this->variableAttribute('rv_color');
    $this->variableAttribute('rv_plain', 'textarea');
    $this->templateAttribute('rv_description');
    $this->templateAttribute('rv_headline', ['value_per_locale' => 1]);
    $this->templateAttribute('rv_banner', ['value_per_channel' => 1]);
    $this->templateAttribute('rv_caption', ['value_per_locale' => 1, 'value_per_channel' => 1]);

    $this->values = [
        'common' => [
            'rv_description' => '{{ rv_name }} / {{ rv_tagline }}',
            'rv_plain'       => '{{ rv_name }} stays text',
            'rv_color'       => 'Red',
        ],
        'locale_specific' => [
            $this->defaultLocale => ['rv_name' => 'Sunglasses', 'rv_headline' => '{{ rv_name | upper }}'],
            'de_DE'              => ['rv_name' => 'Sonnenbrillen', 'rv_headline' => '{{ rv_name | upper }}'],
        ],
        'channel_specific' => [
            $this->defaultChannel       => ['rv_tagline' => 'Everywhere'],
            $this->onlineChannel->code  => ['rv_tagline' => 'Online', 'rv_banner' => '{{ rv_tagline }} ({{ rv_name }})'],
            $this->storeChannel->code   => ['rv_tagline' => 'In store', 'rv_banner' => '{{ rv_tagline }} ({{ rv_name }})'],
        ],
        'channel_locale_specific' => [
            $this->onlineChannel->code => [
                'de_DE' => ['rv_caption' => '{{ rv_name }} @ {{ rv_tagline }}'],
            ],
        ],
    ];

    $this->valuesRenderer = app(ProductValuesRenderer::class);
});

it('renders only the scopes visible in the channel and locale', function () {
    $rendered = $this->valuesRenderer->renderScope($this->values, $this->onlineChannel->code, 'de_DE');

    expect($rendered['common'])->toBe([
        'rv_description' => 'Sonnenbrillen / Online',
        'rv_plain'       => '{{ rv_name }} stays text',
        'rv_color'       => 'Red',
    ])
        ->and($rendered['locale_specific']['de_DE']['rv_headline'])->toBe('SONNENBRILLEN')
        ->and($rendered['locale_specific'][$this->defaultLocale]['rv_headline'])->toBe('{{ rv_name | upper }}')
        ->and($rendered['channel_specific'][$this->onlineChannel->code]['rv_banner'])->toBe('Online (Sonnenbrillen)')
        ->and($rendered['channel_specific'][$this->storeChannel->code]['rv_banner'])->toBe('{{ rv_tagline }} ({{ rv_name }})')
        ->and($rendered['channel_locale_specific'][$this->onlineChannel->code]['de_DE']['rv_caption'])->toBe('Sonnenbrillen @ Online');
});

it('renders the default channel and its default locale when no scope is given', function () {
    $rendered = $this->valuesRenderer->renderScope($this->values);

    expect($rendered['common']['rv_description'])->toBe('Sunglasses / Everywhere');
});

it('renders the locale of the channel when only the channel is given', function () {
    $rendered = $this->valuesRenderer->renderScope($this->values, $this->storeChannel->code);

    expect($rendered['common']['rv_description'])->toBe('Sonnenbrillen / In store')
        ->and($rendered['channel_specific'][$this->storeChannel->code]['rv_banner'])->toBe('In store (Sonnenbrillen)');
});

it('renders every scope in the channel and locale that scope resolves to', function () {
    $rendered = $this->valuesRenderer->renderAllScopes($this->values);

    expect($rendered['common']['rv_description'])->toBe('Sunglasses / Everywhere')
        ->and($rendered['common']['rv_plain'])->toBe('{{ rv_name }} stays text')
        ->and($rendered['locale_specific'][$this->defaultLocale]['rv_headline'])->toBe('SUNGLASSES')
        ->and($rendered['locale_specific']['de_DE']['rv_headline'])->toBe('SONNENBRILLEN')
        ->and($rendered['channel_specific'][$this->onlineChannel->code]['rv_banner'])->toBe('Online (Sunglasses)')
        ->and($rendered['channel_specific'][$this->storeChannel->code]['rv_banner'])->toBe('In store (Sonnenbrillen)')
        ->and($rendered['channel_locale_specific'][$this->onlineChannel->code]['de_DE']['rv_caption'])->toBe('Sonnenbrillen @ Online');
});

it('fills the channel and locale a scope does not carry from the given ones', function () {
    $rendered = $this->valuesRenderer->renderAllScopes($this->values, $this->onlineChannel->code, 'de_DE');

    $storeOnly = $this->valuesRenderer->renderAllScopes($this->values, $this->storeChannel->code);

    expect($rendered['common']['rv_description'])->toBe('Sonnenbrillen / Online')
        ->and($rendered['locale_specific'][$this->defaultLocale]['rv_headline'])->toBe('SUNGLASSES')
        ->and($rendered['channel_specific'][$this->onlineChannel->code]['rv_banner'])->toBe('Online (Sonnenbrillen)')
        ->and($rendered['channel_specific'][$this->storeChannel->code]['rv_banner'])->toBe('In store (Sonnenbrillen)')
        ->and($rendered['channel_locale_specific'][$this->onlineChannel->code]['de_DE']['rv_caption'])->toBe('Sonnenbrillen @ Online')
        ->and($storeOnly['common']['rv_description'])->toBe('Sonnenbrillen / In store')
        ->and($storeOnly['channel_specific'][$this->onlineChannel->code]['rv_banner'])->toBe('Online (Sunglasses)');
});

it('returns the values unchanged when nothing holds a template', function () {
    $values = [
        'common'          => ['rv_color' => 'Red', 'rv_unknown' => '{{ rv_color }}'],
        'locale_specific' => ['de_DE' => ['rv_name' => 'Sonnenbrillen']],
    ];

    expect($this->valuesRenderer->renderScope($values, $this->onlineChannel->code, 'de_DE'))->toBe($values)
        ->and($this->valuesRenderer->renderAllScopes($values))->toBe($values);
});

it('renders a template inherited from the parent with the variant values', function () {
    $parent = Product::factory()->configurable()->create([
        'values' => ['common' => ['rv_description' => '{{ rv_color }} edition']],
    ]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->id,
        'values'    => ['common' => ['sku' => $parent->sku.'-red', 'rv_color' => 'Red']],
    ]);

    expect($variant->renderedValues()['common']['rv_description'])->toBe('Red edition')
        ->and($variant->renderedValues($this->onlineChannel->code, 'de_DE')['common']['rv_description'])->toBe('Red edition')
        ->and($variant->fresh()->values['common'])->not->toHaveKey('rv_description')
        ->and($parent->fresh()->values['common']['rv_description'])->toBe('{{ rv_color }} edition');
});
