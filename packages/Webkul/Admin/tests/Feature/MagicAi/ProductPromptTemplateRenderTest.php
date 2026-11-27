<?php

use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\MagicAI\Models\MagicAIPlatform;
use Webkul\MagicAI\Services\Prompt\ProductPrompt;
use Webkul\MagicAI\Services\Prompt\Prompt;
use Webkul\Product\Models\Product;
use Webkul\Product\Tests\Concerns\InteractsWithTemplates;

uses(InteractsWithTemplates::class);

beforeEach(function () {
    ProductPrompt::resetInstance();

    $this->variableAttribute('tpl_ai_name');
    $this->templateAttribute('tpl_ai_title');
});

afterEach(function () {
    ProductPrompt::resetInstance();
});

it('renders a template the prompt reads', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_ai_name'  => 'Lamp',
        'tpl_ai_title' => '{{ tpl_ai_name | upper }} deal',
    ]]]);

    expect(app(Prompt::class)->getPrompt('Rewrite @tpl_ai_title', $product->id, 'product'))->toBe('Rewrite LAMP deal');
});

it('keeps the template source when the prompt does not render templates', function () {
    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_ai_name'  => 'Lamp',
        'tpl_ai_title' => '{{ tpl_ai_name | upper }} deal',
    ]]]);

    expect(app(Prompt::class)->getPrompt('Translate @tpl_ai_title', $product->id, 'product', renderTemplates: false))
        ->toBe('Translate {{ tpl_ai_name | upper }} deal');
});

it('renders a variant template with the values the variant inherits', function () {
    $parent = Product::factory()->configurable()->create(['values' => ['common' => ['tpl_ai_name' => 'Lamp']]]);

    $variant = Product::factory()->simple()->create([
        'parent_id' => $parent->id,
        'values'    => ['common' => ['tpl_ai_title' => '{{ tpl_ai_name }} deal']],
    ]);

    expect(app(Prompt::class)->getPrompt('Rewrite @tpl_ai_title for @tpl_ai_name', $variant->id, 'product'))
        ->toBe('Rewrite Lamp deal for Lamp');
});

it('sends the template source to the translation model', function () {
    $this->loginAsAdmin();

    MagicAIPlatform::query()->delete();

    MagicAIPlatform::create([
        'label'      => 'Test Platform',
        'provider'   => 'groq',
        'api_url'    => 'https://api.groq.com/openai/v1',
        'api_key'    => 'test-key',
        'models'     => 'qwen-qwq-32b',
        'is_default' => true,
        'status'     => true,
    ]);

    app(CoreConfigRepository::class)->create(['general' => ['magic_ai' => ['settings' => ['enabled' => '1']]]]);

    $product = Product::factory()->simple()->create(['values' => ['common' => [
        'tpl_ai_name'  => 'Lamp',
        'tpl_ai_title' => '{{ tpl_ai_name | upper }} deal',
    ]]]);

    $prompts = [];

    MagicAI::shouldReceive('useDefault')->andReturnSelf();
    MagicAI::shouldReceive('setPlatformId')->andReturnSelf();
    MagicAI::shouldReceive('setModel')->andReturnSelf();
    MagicAI::shouldReceive('setPrompt')->andReturnUsing(function (string $prompt) use (&$prompts) {
        $prompts[] = $prompt;

        return MagicAI::getFacadeRoot();
    });
    MagicAI::shouldReceive('translate')->andReturn('{{ tpl_ai_name | upper }} Angebot');

    $this->post(route('admin.magic_ai.translate'), [
        'resource_id'   => $product->id,
        'resource_type' => 'product',
        'field'         => 'tpl_ai_title',
        'model'         => 'qwen-qwq-32b',
        'targetLocale'  => 'de_DE',
    ])->assertOk();

    expect($prompts)->toHaveCount(1)
        ->and($prompts[0])->toContain('{{ tpl_ai_name | upper }} deal')
        ->and($prompts[0])->not->toContain('LAMP deal');
});
