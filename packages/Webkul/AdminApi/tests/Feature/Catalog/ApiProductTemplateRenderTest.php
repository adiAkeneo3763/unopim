<?php

use Webkul\Core\Models\Locale;
use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;

uses(InteractsWithTemplates::class);

beforeEach(function () {
    $this->headers = $this->getAuthenticationHeaders();

    $this->channel = $this->templateChannel(['EUR'], ['en_US', 'de_DE']);
    $this->defaultLocale = core()->getDefaultLocaleCodeFromDefaultChannel();

    $this->variableAttribute('tpl_api_brand');
    $this->variableAttribute('tpl_api_name', 'text', ['value_per_locale' => 1]);
    $this->variableAttribute('tpl_api_tagline', 'text', ['value_per_channel' => 1]);
    $this->templateAttribute('tpl_api_title');
    $this->templateAttribute('tpl_api_headline', ['value_per_locale' => 1]);
    $this->templateAttribute('tpl_api_banner', ['value_per_channel' => 1]);

    $this->values = [
        'common' => [
            'tpl_api_brand' => 'Acme',
            'tpl_api_title' => '{{ tpl_api_name }} by {{ tpl_api_brand | upper }}',
        ],
        'locale_specific' => [
            $this->defaultLocale => ['tpl_api_name' => 'Sunglasses', 'tpl_api_headline' => '{{ tpl_api_name | upper }}'],
            'de_DE'              => ['tpl_api_name' => 'Sonnenbrille', 'tpl_api_headline' => '{{ tpl_api_name | upper }}'],
        ],
        'channel_specific' => [
            $this->channel->code => ['tpl_api_tagline' => 'Online', 'tpl_api_banner' => '{{ tpl_api_tagline }}: {{ tpl_api_name }}'],
        ],
    ];
});

it('returns rendered templates of every scope and keeps the stored sources', function () {
    $product = Product::factory()->simple()->create(['values' => $this->values]);

    $stored = $product->fresh()->values;

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.get', ['code' => $product->sku]))
        ->assertOk()
        ->assertJsonPath('values.common.tpl_api_title', 'Sunglasses by ACME')
        ->assertJsonPath('values.common.tpl_api_brand', 'Acme')
        ->assertJsonPath('values.locale_specific.de_DE.tpl_api_headline', 'SONNENBRILLE')
        ->assertJsonPath('values.locale_specific.'.$this->defaultLocale.'.tpl_api_headline', 'SUNGLASSES')
        ->assertJsonPath('values.channel_specific.'.$this->channel->code.'.tpl_api_banner', 'Online: Sunglasses');

    expect($product->fresh()->values)->toBe($stored);
});

it('returns the stored template sources when rendering is turned off', function () {
    $product = Product::factory()->simple()->create(['values' => $this->values]);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.get', ['code' => $product->sku, 'render_templates' => 0]))
        ->assertOk()
        ->assertJsonPath('values.common.tpl_api_title', '{{ tpl_api_name }} by {{ tpl_api_brand | upper }}')
        ->assertJsonPath('values.locale_specific.de_DE.tpl_api_headline', '{{ tpl_api_name | upper }}')
        ->assertJsonPath('values.channel_specific.'.$this->channel->code.'.tpl_api_banner', '{{ tpl_api_tagline }}: {{ tpl_api_name }}');
});

it('renders templates in the product listing', function () {
    $product = Product::factory()->simple()->create(['values' => $this->values]);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.index', [
            'filters' => json_encode(['sku' => [['operator' => '=', 'value' => $product->sku]]]),
        ]))
        ->assertOk()
        ->assertJsonPath('data.0.sku', $product->sku)
        ->assertJsonPath('data.0.values.common.tpl_api_title', 'Sunglasses by ACME')
        ->assertJsonPath('data.0.values.channel_specific.'.$this->channel->code.'.tpl_api_banner', 'Online: Sunglasses');
});

it('renders a template inherited from the parent with the values of the variant', function () {
    $parent = Product::factory()->configurable()->create(['sku' => 'TPL-API-PARENT', 'values' => ['common' => [
        'sku'           => 'TPL-API-PARENT',
        'tpl_api_brand' => 'Acme',
        'tpl_api_title' => '{{ tpl_api_brand }} {{ sku }}',
    ]]]);

    $variant = Product::factory()->simple()->create([
        'sku'       => 'TPL-API-RED',
        'parent_id' => $parent->id,
        'values'    => ['common' => ['sku' => 'TPL-API-RED']],
    ]);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.get', ['code' => $variant->sku]))
        ->assertOk()
        ->assertJsonPath('values.common.tpl_api_title', 'Acme TPL-API-RED');

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.configurable_products.get', ['code' => $parent->sku]))
        ->assertOk()
        ->assertJsonPath('values.common.tpl_api_title', 'Acme TPL-API-PARENT');

    expect($variant->fresh()->values['common'])->not->toHaveKey('tpl_api_title')
        ->and($parent->fresh()->values['common']['tpl_api_title'])->toBe('{{ tpl_api_brand }} {{ sku }}');
});

it('renders the scopes without a channel or locale in the requested ones', function () {
    $product = Product::factory()->simple()->create(['values' => $this->values]);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.get', ['code' => $product->sku, 'channel' => $this->channel->code, 'locale' => 'de_DE']))
        ->assertOk()
        ->assertJsonPath('values.common.tpl_api_title', 'Sonnenbrille by ACME')
        ->assertJsonPath('values.locale_specific.'.$this->defaultLocale.'.tpl_api_headline', 'SUNGLASSES')
        ->assertJsonPath('values.channel_specific.'.$this->channel->code.'.tpl_api_banner', 'Online: Sonnenbrille');
});

it('rejects an unknown channel or locale', function (array $scope, string $field) {
    $product = Product::factory()->simple()->create(['values' => $this->values]);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.get', ['code' => $product->sku, ...$scope]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.index', $scope))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'unknown channel' => [['channel' => 'tpl_api_missing'], 'channel'],
    'unknown locale'  => [['locale' => 'xx_XX'], 'locale'],
]);

it('rejects an inactive locale', function () {
    $locale = Locale::factory()->create(['status' => 0]);

    $this->withHeaders($this->headers)
        ->json('GET', route('admin.api.products.index', ['locale' => $locale->code]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('locale');
});
