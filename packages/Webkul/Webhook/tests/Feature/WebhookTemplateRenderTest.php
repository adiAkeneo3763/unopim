<?php

use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;
use Webkul\Webhook\Services\WebhookService;

uses(InteractsWithTemplates::class);

beforeEach(function () {
    $this->variableAttribute('tpl_wh_name');
    $this->variableAttribute('tpl_wh_color');
    $this->templateAttribute('tpl_wh_title');
});

it('reports a changed template with its rendered old and new values', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_wh_name'  => 'Lamp',
        'tpl_wh_title' => '{{ tpl_wh_name }} deal',
    ]]]);

    $product->update(['values' => ['common' => [
        'tpl_wh_name'  => 'Lamp',
        'tpl_wh_title' => '{{ tpl_wh_name | upper }} deal',
    ]]]);

    $changes = app(WebhookService::class)->getProductChangesForWebhook($product);

    expect($changes['changed']['common']['tpl_wh_title'])->toBe(['old' => 'Lamp deal', 'new' => 'LAMP deal'])
        ->and(json_encode($changes))->not->toContain('{{')
        ->and($product->fresh()->values['common']['tpl_wh_title'])->toBe('{{ tpl_wh_name | upper }} deal');
});

it('reports a template whose output changed because a variable it reads changed', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_wh_name'  => 'Lamp',
        'tpl_wh_title' => '{{ tpl_wh_name }} deal',
    ]]]);

    $product->update(['values' => ['common' => [
        'tpl_wh_name'  => 'Desk Lamp',
        'tpl_wh_title' => '{{ tpl_wh_name }} deal',
    ]]]);

    $changes = app(WebhookService::class)->getProductChangesForWebhook($product);

    expect($changes['changed']['common']['tpl_wh_name'])->toBe(['old' => 'Lamp', 'new' => 'Desk Lamp'])
        ->and($changes['changed']['common']['tpl_wh_title'])->toBe(['old' => 'Lamp deal', 'new' => 'Desk Lamp deal']);
});

it('reports nothing when a template source changes but its output does not', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_wh_name'  => 'lamp',
        'tpl_wh_title' => '{{ tpl_wh_name }} deal',
    ]]]);

    $product->update(['values' => ['common' => [
        'tpl_wh_name'  => 'lamp',
        'tpl_wh_title' => '{{ tpl_wh_name | lower }} deal',
    ]]]);

    expect(app(WebhookService::class)->getProductChangesForWebhook($product))->toBe([]);
});

it('reports a created product with its rendered templates', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_wh_name'  => 'Lamp',
        'tpl_wh_title' => '{{ tpl_wh_name | upper }} deal',
    ]]]);

    $changes = app(WebhookService::class)->getProductChangesForWebhook($product);

    expect($changes['added']['common']['tpl_wh_title'])->toBe('LAMP deal');
});

it('renders a template of the variant with the values it inherits', function () {
    $parent = Product::factory()->configurable()->create(['values' => ['common' => ['tpl_wh_name' => 'Lamp']]]);

    $variant = Product::factory()->simple()->create([
        'parent_id' => $parent->id,
        'values'    => ['common' => ['tpl_wh_title' => '{{ tpl_wh_name }} deal']],
    ]);

    $variant->update(['values' => ['common' => ['tpl_wh_title' => '{{ tpl_wh_name | upper }} deal']]]);

    $changes = app(WebhookService::class)->getProductChangesForWebhook($variant);

    expect($changes['changed']['common']['tpl_wh_title'])->toBe(['old' => 'Lamp deal', 'new' => 'LAMP deal'])
        ->and($changes['changed']['common'])->not->toHaveKey('tpl_wh_name')
        ->and($changes['added'])->toBe([]);
});

it('does not report a template the variant only inherits', function () {
    $parent = Product::factory()->configurable()->create(['values' => ['common' => [
        'tpl_wh_title' => 'Color is {{ tpl_wh_color }}',
    ]]]);

    $variant = Product::factory()->simple()->create([
        'parent_id' => $parent->id,
        'values'    => ['common' => ['tpl_wh_color' => 'Graphite']],
    ]);

    $variant->update(['values' => ['common' => ['tpl_wh_color' => 'Silver']]]);

    $changes = app(WebhookService::class)->getProductChangesForWebhook($variant);

    expect($changes['changed']['common'])->toBe(['tpl_wh_color' => ['old' => 'Graphite', 'new' => 'Silver']]);
});
