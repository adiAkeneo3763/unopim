<?php

use Webkul\Core\Facades\ElasticSearch;
use Webkul\DataTransfer\Helpers\Importers\Product\Importer;
use Webkul\DataTransfer\Models\JobTrack;
use Webkul\DataTransfer\Models\JobTrackBatch;
use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;

uses(InteractsWithTemplates::class);

beforeEach(function () {
    config([
        'elasticsearch.enabled'                     => true,
        'elasticsearch.prefix'                      => 'testing',
        'elasticsearch.connection'                  => 'default',
        'elasticsearch.connections.default.hosts.0' => 'testhost:9200',
    ]);

    ElasticSearch::shouldReceive('makeConnection')
        ->andReturn(Mockery::mock('Webkul\ElasticSearch\Client\Fake\FakeElasticClient'))
        ->zeroOrMoreTimes();

    $this->variableAttribute('tpl_es_brand');
    $this->variableAttribute('tpl_es_name', 'text', ['value_per_locale' => 1]);
    $this->templateAttribute('tpl_es_title');
    $this->templateAttribute('tpl_es_headline', ['value_per_locale' => 1]);
});

function templateIndexFamily(): array
{
    config(['elasticsearch.enabled' => false]);

    $parent = Product::factory()->configurable()->create(['sku' => 'TPL-ES-PARENT', 'values' => ['common' => [
        'sku'          => 'TPL-ES-PARENT',
        'tpl_es_brand' => 'Acme',
        'tpl_es_title' => '{{ tpl_es_brand | upper }} {{ sku }}',
    ]]]);

    $variant = Product::factory()->simple()->create([
        'sku'       => 'TPL-ES-RED',
        'parent_id' => $parent->id,
        'values'    => ['common' => ['sku' => 'TPL-ES-RED']],
    ]);

    config(['elasticsearch.enabled' => true]);

    return [$parent, $variant];
}

it('indexes the rendered templates of every scope when a product is saved', function () {
    $indexed = null;

    ElasticSearch::shouldReceive('index')
        ->once()
        ->withArgs(function (array $args) use (&$indexed): bool {
            $indexed = $args['body']['values'];

            return true;
        });

    $product = Product::factory()->simple()->create(['sku' => 'TPL-ES-SIMPLE', 'values' => [
        'common' => [
            'sku'          => 'TPL-ES-SIMPLE',
            'tpl_es_brand' => 'Acme',
            'tpl_es_title' => '{{ tpl_es_brand | upper }} {{ sku }}',
        ],
        'locale_specific' => [
            'de_DE' => ['tpl_es_name' => 'Sonnenbrille', 'tpl_es_headline' => '{{ tpl_es_name | upper }}'],
        ],
    ]]);

    expect($indexed['common']['tpl_es_title-textarea'])->toBe('ACME TPL-ES-SIMPLE')
        ->and($indexed['locale_specific']['de_DE']['tpl_es_headline-textarea'])->toBe('SONNENBRILLE')
        ->and($product->fresh()->values['common']['tpl_es_title'])->toBe('{{ tpl_es_brand | upper }} {{ sku }}');
});

it('indexes a template inherited from the parent with the values of the variant when the variant is saved', function () {
    [, $variant] = templateIndexFamily();

    $indexed = null;

    ElasticSearch::shouldReceive('index')
        ->once()
        ->withArgs(function (array $args) use (&$indexed): bool {
            $indexed = $args['body']['values'];

            return true;
        });

    $variant->status = 0;

    $variant->save();

    expect($indexed['common']['tpl_es_title-textarea'])->toBe('ACME TPL-ES-RED')
        ->and($variant->fresh()->values['common'])->not->toHaveKey('tpl_es_title');
});

it('indexes rendered and inherited templates when the whole catalog is reindexed', function () {
    [$parent, $variant] = templateIndexFamily();

    $indicesMock = Mockery::mock('Elastic\Elasticsearch\Endpoints\Indices');

    $existsResponse = Mockery::mock('Elastic\Elasticsearch\Response\Elasticsearch');

    $existsResponse->shouldReceive('asBool')->andReturn(true);

    $indicesMock->shouldReceive('exists')->andReturn($existsResponse);
    $indicesMock->shouldReceive('putSettings')->andReturn([]);
    $indicesMock->shouldReceive('delete')->andReturn([]);
    $indicesMock->shouldReceive('create')->andReturn([]);

    ElasticSearch::shouldReceive('indices')->andReturn($indicesMock);

    $documents = [];

    ElasticSearch::shouldReceive('bulk')->andReturnUsing(function (array $args) use (&$documents): array {
        foreach (array_chunk($args['body'], 2) as [$action, $document]) {
            $documents[$action['index']['_id']] = $document;
        }

        return ['errors' => false, 'items' => []];
    });

    Artisan::call('unopim:product:index', ['--fresh' => true]);

    expect($documents[$parent->id]['values']['common']['tpl_es_title-textarea'])->toBe('ACME TPL-ES-PARENT')
        ->and($documents[$variant->id]['values']['common']['tpl_es_title-textarea'])->toBe('ACME TPL-ES-RED');
});

it('indexes rendered and inherited templates for an import batch', function () {
    [$parent, $variant] = templateIndexFamily();

    $batch = JobTrackBatch::factory()->create([
        'job_track_id' => JobTrack::factory()->create()->id,
        'data'         => [['sku' => $parent->sku], ['sku' => $variant->sku]],
    ]);

    $documents = [];

    ElasticSearch::shouldReceive('bulk')
        ->once()
        ->withArgs(function (array $args) use (&$documents): bool {
            foreach (array_chunk($args['body'], 2) as [$action, $document]) {
                $documents[$action['index']['_id']] = $document;
            }

            return true;
        })
        ->andReturn(['errors' => false, 'items' => []]);

    app(Importer::class)->indexBatch($batch);

    expect($documents[$parent->id]['values']['common']['tpl_es_title-textarea'])->toBe('ACME TPL-ES-PARENT')
        ->and($documents[$variant->id]['values']['common']['tpl_es_title-textarea'])->toBe('ACME TPL-ES-RED');
});
