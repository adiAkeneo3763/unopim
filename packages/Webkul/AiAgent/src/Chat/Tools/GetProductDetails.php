<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Concerns\ChecksPermission;
use Webkul\AiAgent\Chat\Contracts\PimTool;
use Webkul\Product\Contracts\ProductValuesRenderer;
use Webkul\Product\Templates\TemplateParser;

class GetProductDetails implements PimTool
{
    public function register(ChatContext $context): Tool
    {
        return new class($context) extends ContextualTool
        {
            use ChecksPermission;

            public function name(): string
            {
                return 'get_product_details';
            }

            public function description(): string
            {
                return 'Get full product details by SKU or ID. Values show rendered template output; template_sources lists the fields that hold a template with their source, so edit that source instead of the rendered text.';
            }

            public function schema(JsonSchema $schema): array
            {
                return [
                    'sku'        => $schema->string()->description('Product SKU (preferred)'),
                    'product_id' => $schema->integer()->description('Product ID (alternative to SKU)'),
                ];
            }

            public function handle(Request $request): string
            {
                if ($denied = $this->denyUnlessAllowed($this->context, 'catalog.products')) {
                    return $denied;
                }

                $sku = $request->string('sku')->toString() ?: null;
                $productId = $request->has('product_id') ? $request->integer('product_id') : null;

                $product = null;

                if ($sku) {
                    $product = DB::table('products')->where('sku', $sku)->first();
                } elseif ($productId) {
                    $product = DB::table('products')->where('id', $productId)->first();
                }

                if (! $product) {
                    return json_encode(['error' => 'Product not found']);
                }

                $values = json_decode((string) $product->values, true) ?? [];
                $family = DB::table('attribute_families')
                    ->where('id', $product->attribute_family_id)
                    ->value('code');

                $details = [
                    'id'     => $product->id,
                    'sku'    => $product->sku,
                    'type'   => $product->type,
                    'status' => $product->status ? 'active' : 'inactive',
                    'family' => $family,
                    'values' => resolve(ProductValuesRenderer::class)->renderOwnRows([$product])[$product->id] ?? $values,
                ];

                $templateParser = resolve(TemplateParser::class);

                $templateSources = array_filter(Arr::dot($values), fn (mixed $value): bool => $templateParser->containsTemplate($value));

                if ($templateSources !== []) {
                    $details['template_sources'] = $templateSources;
                }

                return json_encode($details);
            }
        };
    }
}
