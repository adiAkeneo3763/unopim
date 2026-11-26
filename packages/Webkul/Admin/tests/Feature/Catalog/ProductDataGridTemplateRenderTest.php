<?php

use Mockery\MockInterface;
use Webkul\Admin\DataGrids\Catalog\ProductDataGrid;
use Webkul\Core\Facades\ElasticSearch;
use Webkul\Product\Contracts\TemplateRenderer;
use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;

uses(InteractsWithTemplates::class);

beforeEach(function () {
    config(['elasticsearch.enabled' => false]);

    $this->loginAsAdmin();

    $this->channel = $this->templateChannel(['EUR', 'USD'], ['en_US', 'de_DE']);

    $color = $this->variableAttribute('tpl_grid_color', 'select');

    $this->attributeOption($color, 'blue', ['en_US' => 'Blue', 'de_DE' => 'Blau']);

    $this->variableAttribute('tpl_grid_brand');
    $this->variableAttribute('tpl_grid_name', 'text', ['value_per_locale' => 1]);
    $this->variableAttribute('tpl_grid_tagline', 'text', ['value_per_channel' => 1]);
    $this->variableAttribute('tpl_grid_price', 'price');
    $this->variableAttribute('tpl_grid_title', 'text', ['enable_templates' => 1]);
    $this->variableAttribute('tpl_grid_headline', 'text', ['enable_templates' => 1, 'value_per_locale' => 1, 'value_per_channel' => 1]);

    $this->names = [
        'en_US' => ['tpl_grid_name' => 'Sunglasses'],
        'de_DE' => ['tpl_grid_name' => 'Sonnenbrille'],
    ];
});

function templateGridRecords(array $productIds, array $columns, string $channel, string $locale): array
{
    request()->replace([
        'managedColumns' => ['product_id', 'sku', ...$columns],
        'productIds'     => $productIds,
        'channel'        => $channel,
        'locale'         => $locale,
    ]);

    $grid = app(ProductDataGrid::class);

    $grid->prepare();

    return collect(json_decode(json_encode($grid->formatData()['records']), true))
        ->keyBy('product_id')
        ->all();
}

it('shows rendered templates for the grid channel and locale and keeps the stored sources', function () {
    $product = Product::factory()->create(['values' => [
        'common' => [
            'tpl_grid_brand' => 'Acme',
            'tpl_grid_price' => ['EUR' => '110', 'USD' => '120'],
            'tpl_grid_title' => '{{ tpl_grid_name }} by {{ tpl_grid_brand | upper }} for {{ tpl_grid_price.EUR }}',
        ],
        'locale_specific'         => $this->names,
        'channel_specific'        => [$this->channel->code => ['tpl_grid_tagline' => 'Online only']],
        'channel_locale_specific' => [$this->channel->code => [
            'en_US' => ['tpl_grid_headline' => '{{ tpl_grid_tagline }}!'],
            'de_DE' => ['tpl_grid_headline' => 'Nur {{ tpl_grid_tagline | lower }}'],
        ]],
    ]]);

    $stored = $product->fresh()->values;

    $columns = ['tpl_grid_title', 'tpl_grid_headline'];

    $english = templateGridRecords([$product->id], $columns, $this->channel->code, 'en_US')[$product->id];

    $german = templateGridRecords([$product->id], $columns, $this->channel->code, 'de_DE')[$product->id];

    expect($english['tpl_grid_title'])->toBe('Sunglasses by ACME for 110 EUR')
        ->and($english['tpl_grid_headline'])->toBe('Online only!')
        ->and($german['tpl_grid_title'])->toBe('Sonnenbrille by ACME for 110 EUR')
        ->and($german['tpl_grid_headline'])->toBe('Nur online only')
        ->and($product->fresh()->values)->toBe($stored);
});

it('renders a template inherited from the parent with the values of the variant row', function () {
    $parent = Product::factory()->configurable()->create(['values' => [
        'common' => [
            'tpl_grid_brand' => 'Acme',
            'tpl_grid_title' => '{{ tpl_grid_brand }} {{ tpl_grid_color }}',
        ],
    ]]);

    $variant = Product::factory()->create([
        'parent_id' => $parent->id,
        'values'    => ['common' => ['tpl_grid_color' => 'blue']],
    ]);

    $storedVariant = $variant->fresh()->values;

    $english = templateGridRecords([$parent->id, $variant->id], ['tpl_grid_title'], $this->channel->code, 'en_US');

    $german = templateGridRecords([$variant->id], ['tpl_grid_title'], $this->channel->code, 'de_DE');

    expect($english[$variant->id]['tpl_grid_title'])->toBe('Acme Blue')
        ->and($german[$variant->id]['tpl_grid_title'])->toBe('Acme Blau')
        ->and($english[$parent->id]['tpl_grid_title'])->not->toContain('{{')
        ->and($variant->fresh()->values)->toBe($storedVariant);
});

it('renders templates in the grid when Elasticsearch is enabled', function () {
    $product = Product::factory()->create(['values' => [
        'common'          => ['tpl_grid_brand' => 'Acme', 'tpl_grid_title' => '{{ tpl_grid_name }} by {{ tpl_grid_brand }}'],
        'locale_specific' => $this->names,
    ]]);

    config([
        'elasticsearch.enabled'                     => true,
        'elasticsearch.prefix'                      => 'testing',
        'elasticsearch.connection'                  => 'default',
        'elasticsearch.connections.default.hosts.0' => 'testhost:9200',
    ]);

    ElasticSearch::shouldReceive('makeConnection')
        ->andReturn(Mockery::mock('Webkul\ElasticSearch\Client\Fake\FakeElasticClient'));

    ElasticSearch::shouldReceive('search')
        ->once()
        ->withArgs(fn (array $args): bool => $args['index'] === 'testing_products')
        ->andReturn(['hits' => ['total' => ['value' => 1], 'hits' => [['_id' => (string) $product->id]]]]);

    $record = templateGridRecords([$product->id], ['tpl_grid_title'], $this->channel->code, 'de_DE')[$product->id];

    expect($record['tpl_grid_title'])->toBe('Sonnenbrille by Acme');
});

it('skips the template renderer when no listed product holds a template', function () {
    $this->mock(TemplateRenderer::class, fn (MockInterface $mock): mixed => $mock->shouldNotReceive('render'));

    $product = Product::factory()->create(['values' => [
        'common'          => ['tpl_grid_brand' => 'Acme'],
        'locale_specific' => $this->names,
    ]]);

    $record = templateGridRecords([$product->id], ['tpl_grid_brand', 'tpl_grid_name'], $this->channel->code, 'de_DE')[$product->id];

    expect($record['tpl_grid_brand'])->toBe('Acme')
        ->and($record['tpl_grid_name'])->toBe('Sonnenbrille');
});
