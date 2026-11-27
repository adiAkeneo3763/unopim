<?php

use Webkul\Attribute\Models\AttributeFamilyProxy;
use Webkul\Core\Models\Locale;
use Webkul\Product\Models\ProductProxy;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;
use Webkul\ProductPassport\Services\PassportPayloadBuilder;
use Webkul\Publication\Services\Publisher;

uses(InteractsWithTemplates::class);

beforeEach(function (): void {
    $this->variableAttribute('tpl_dpp_name');
    $this->variableAttribute('tpl_dpp_local_name', 'text', ['value_per_locale' => 1]);

    $this->family = AttributeFamilyProxy::factory()->withMinimalAttributesForProductTypes()->create();

    $this->passportTemplateFor($this->family, [
        'dpp_title' => ['attribute' => $this->templateAttribute('tpl_dpp_title')],
        'dpp_care'  => ['attribute' => $this->templateAttribute('tpl_dpp_care', ['enable_wysiwyg' => 1])],
    ]);
});

it('publishes the rendered template value and keeps the source on the product', function (): void {
    $product = ProductProxy::factory()->create([
        'attribute_family_id' => $this->family->id,
        'values'              => ['common' => ['tpl_dpp_name' => 'Lamp', 'tpl_dpp_title' => '{{ tpl_dpp_name | upper }} passport']],
    ]);

    $payload = resolve(PassportPayloadBuilder::class)->build($product, $this->publicationContextFor($product));

    expect(collect($payload['sections'][0]['fields'])->firstWhere('code', 'dpp_title')['value'])->toBe('LAMP passport')
        ->and(json_encode($payload))->not->toContain('{{')
        ->and($product->fresh()->values['common']['tpl_dpp_title'])->toBe('{{ tpl_dpp_name | upper }} passport');
});

it('renders the template with the value of the passport locale', function (): void {
    $product = ProductProxy::factory()->create([
        'attribute_family_id' => $this->family->id,
        'values'              => [
            'common'          => ['tpl_dpp_title' => '{{ tpl_dpp_local_name }} passport'],
            'locale_specific' => [
                'en_US' => ['tpl_dpp_local_name' => 'Lamp'],
                'de_DE' => ['tpl_dpp_local_name' => 'Lampe'],
            ],
        ],
    ]);

    $context = $this->publicationContextFor($product, $this->templateChannel(), Locale::query()->where('code', 'de_DE')->firstOrFail());

    $payload = resolve(PassportPayloadBuilder::class)->build($product, $context);

    expect(collect($payload['sections'][0]['fields'])->firstWhere('code', 'dpp_title')['value'])->toBe('Lampe passport');
});

it('renders the template a variant inherits with the variant values', function (): void {
    $parent = ProductProxy::factory()->configurable()->create([
        'attribute_family_id' => $this->family->id,
        'values'              => ['common' => ['tpl_dpp_name' => 'Lamp', 'tpl_dpp_title' => '{{ tpl_dpp_name }} passport']],
    ]);

    $variant = ProductProxy::factory()->create([
        'attribute_family_id' => $this->family->id,
        'parent_id'           => $parent->id,
        'values'              => ['common' => ['tpl_dpp_name' => 'Desk Lamp']],
    ]);

    $payload = resolve(PassportPayloadBuilder::class)->build($variant, $this->publicationContextFor($variant));

    expect(collect($payload['sections'][0]['fields'])->firstWhere('code', 'dpp_title')['value'])->toBe('Desk Lamp passport');
});

it('renders a rich-text template before it sanitizes it', function (): void {
    $product = ProductProxy::factory()->create([
        'attribute_family_id' => $this->family->id,
        'values'              => ['common' => [
            'tpl_dpp_name' => 'Lamp',
            'tpl_dpp_care' => '<p>Wash the {{ tpl_dpp_name }} by hand.</p><script>alert(1)</script>',
        ]],
    ]);

    $payload = resolve(PassportPayloadBuilder::class)->build($product, $this->publicationContextFor($product));

    $field = collect($payload['sections'][0]['fields'])->firstWhere('code', 'dpp_care');

    expect($field['value'])->toContain('<p>Wash the Lamp by hand.</p>')
        ->and($field['value'])->not->toContain('<script>')
        ->and($field['value_text'])->toBe('Wash the Lamp by hand.');
});

it('mints no new version when a passport with a template is published again', function (): void {
    $product = ProductProxy::factory()->create([
        'attribute_family_id' => $this->family->id,
        'values'              => ['common' => ['tpl_dpp_name' => 'Lamp', 'tpl_dpp_title' => '{{ tpl_dpp_name }} passport']],
    ]);

    $context = $this->publicationContextFor($product);

    $this->enablePublicTier($context->channel->code);
    $this->enablePassportPublishing($context->channel->code);

    $publisher = resolve(Publisher::class);

    $version = $publisher->publish($product, $context->channel, $context->locale, 'dpp');

    expect($version)->not->toBeNull()
        ->and(json_encode($version->payload))->not->toContain('{{')
        ->and($publisher->publish($product, $context->channel, $context->locale, 'dpp'))->toBeNull();
});
