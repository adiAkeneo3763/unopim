<?php

use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;
use Webkul\Admin\Tests\Support\InMemoryEmbeddingElasticSearch;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Tools\ExportProducts;
use Webkul\AiAgent\Chat\Tools\GetProductDetails;
use Webkul\AiAgent\Jobs\IndexProductEmbeddingsJob;
use Webkul\AiAgent\Services\VectorStore\ProductEmbeddingDocumentBuilder;
use Webkul\AiAgent\Services\VectorStore\ProductEmbeddingIndex;
use Webkul\Attribute\Models\Attribute;
use Webkul\MagicAI\Models\MagicAIPlatform;
use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;
use Webkul\User\Models\Admin;

uses(InteractsWithTemplates::class);

function templateRenderChatContext(): ChatContext
{
    return new ChatContext(
        message: 'Show the product',
        history: [],
        productId: null,
        productSku: null,
        productName: null,
        locale: 'en_US',
        channel: 'default',
        platform: new MagicAIPlatform(['provider' => 'openai', 'models' => 'gpt-4o']),
        model: 'gpt-4o',
        user: Admin::query()->firstOrFail(),
    );
}

function templateRenderProductDetails(Product $product): array
{
    $tool = resolve(GetProductDetails::class)->register(templateRenderChatContext());

    return json_decode((string) $tool->handle(new Request(['sku' => $product->sku])), true);
}

beforeEach(function () {
    $this->variableAttribute('tpl_ai_name');
    $this->templateAttribute('tpl_ai_title');
});

it('returns rendered values and the template sources from the product details tool', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_ai_name'  => 'Lamp',
        'tpl_ai_title' => '{{ tpl_ai_name }} deal',
    ]]]);

    $details = templateRenderProductDetails($product);

    expect($details['values']['common']['tpl_ai_title'])->toBe('Lamp deal')
        ->and($details['values']['common']['tpl_ai_name'])->toBe('Lamp')
        ->and($details['template_sources'])->toBe(['common.tpl_ai_title' => '{{ tpl_ai_name }} deal'])
        ->and($product->fresh()->values['common']['tpl_ai_title'])->toBe('{{ tpl_ai_name }} deal');
});

it('renders a variant template with inherited values and returns only the keys the variant owns', function () {
    $parent = Product::factory()->configurable()->create([
        'values' => ['common' => ['tpl_ai_name' => 'Lamp']],
    ]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->id,
        'values'    => ['common' => ['sku' => $parent->sku.'-red', 'tpl_ai_title' => '{{ tpl_ai_name }} deal']],
    ]);

    $details = templateRenderProductDetails($variant);

    expect($details['values']['common']['tpl_ai_title'])->toBe('Lamp deal')
        ->and($details['values']['common'])->not->toHaveKey('tpl_ai_name');
});

it('leaves the product details without template sources when no value holds a template', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => ['tpl_ai_name' => 'Lamp']]]);

    expect(templateRenderProductDetails($product))->not->toHaveKey('template_sources');
});

it('writes the rendered description into the chat export rows', function () {
    Attribute::query()->where('code', 'description')->update(['enable_templates' => 1, 'validation' => null]);

    $product = Product::factory()->simple()->create(['values' => [
        'common'                  => ['tpl_ai_name' => 'Lamp'],
        'channel_locale_specific' => ['default' => ['en_US' => ['description' => '<p>{{ tpl_ai_name }} for every desk</p>']]],
    ]]);

    $exportProducts = resolve(ExportProducts::class);

    $rows = $exportProducts->buildCsvRows(
        $exportProducts->collectProducts(['skus' => [$product->sku], 'status' => 'all']),
        templateRenderChatContext(),
    );

    expect($rows[1][0])->toBe($product->sku)
        ->and($rows[1][4])->toBe('<p>Lamp for every desk</p>');
});

it('embeds the rendered template output instead of the source', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_ai_name'  => 'Lamp',
        'tpl_ai_title' => '{{ tpl_ai_name }} deal',
    ]]]);

    config(['ai-agent.vector_store.enabled' => true, 'elasticsearch.enabled' => true]);

    (new InMemoryEmbeddingElasticSearch)->install();

    Embeddings::fake([[array_fill(0, 8, 0.5)]]);

    (new IndexProductEmbeddingsJob([$product->id]))->handle(new ProductEmbeddingIndex, new ProductEmbeddingDocumentBuilder);

    Embeddings::assertGenerated(fn ($prompt): bool => $prompt->contains('tpl_ai_title: Lamp deal') && ! $prompt->contains('{{'));
});
