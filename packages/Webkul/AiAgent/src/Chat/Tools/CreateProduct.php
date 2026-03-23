<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;
use Webkul\AiAgent\Services\ProductWriterService;
use Webkul\Core\Filesystem\FileStorer;

class CreateProduct implements PimTool
{
    public function __construct(
        protected ProductWriterService $writerService,
    ) {}

    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('create_product')
            ->for('Create a product with attributes, categories, and image. Pass all attributes in attributes_json.')
            ->withStringParameter('sku', 'Product SKU (must be unique). Auto-generated if not provided.')
            ->withStringParameter('name', 'Product name (required)')
            ->withStringParameter('description', 'Product description')
            ->withStringParameter('short_description', 'Short product description')
            ->withStringParameter('meta_title', 'SEO meta title')
            ->withStringParameter('meta_description', 'SEO meta description')
            ->withStringParameter('meta_keywords', 'SEO meta keywords')
            ->withStringParameter('categories', 'Comma-separated category codes or paths to assign (e.g. "Electronics,Clothing" or "Electronics > Laptops")')
            ->withBooleanParameter('attach_image', 'Set to true to attach the uploaded image to this product (default: true)')
            ->withStringParameter('attributes_json', 'JSON string of ALL additional attribute values including color, size, brand, price, cost, product_number, etc. Example: {"color":"Red","size":"M","brand":"Nike","price":49.99,"cost":25,"product_number":"PRD-001"}')
            ->using(function (
                ?string $sku = null,
                ?string $name = null,
                ?string $description = null,
                ?string $short_description = null,
                ?string $meta_title = null,
                ?string $meta_description = null,
                ?string $meta_keywords = null,
                ?string $categories = null,
                bool $attach_image = true,
                ?string $attributes_json = null,
            ) use ($context): string {
                $extraAttrs = $attributes_json ? (json_decode($attributes_json, true) ?? []) : [];

                if (! $name && ! empty($extraAttrs['detected_name'])) {
                    $name = $extraAttrs['detected_name'];
                }

                if (! $name && ! empty($extraAttrs['name'])) {
                    $name = $extraAttrs['name'];
                }

                if (! $name) {
                    return json_encode(['error' => 'Product name is required']);
                }

                $sku = $sku ?: Str::slug($name).'-'.strtoupper(Str::random(6));

                if (DB::table('products')->where('sku', $sku)->exists()) {
                    return json_encode(['error' => "SKU '{$sku}' already exists"]);
                }

                $familyId = DB::table('attribute_families')->value('id') ?? 1;

                $repo = app('Webkul\Product\Repositories\ProductRepository');
                $product = $repo->create([
                    'sku'                 => $sku,
                    'type'                => 'simple',
                    'attribute_family_id' => $familyId,
                ]);

                // Merge all attributes: explicit params + extra attributes from JSON
                $allAttrs = array_filter(array_merge($extraAttrs, [
                    'name'              => $name,
                    'description'       => $description,
                    'short_description' => $short_description,
                    'meta_title'        => $meta_title,
                    'meta_description'  => $meta_description,
                    'meta_keywords'     => $meta_keywords,
                ]), fn ($v) => $v !== null && $v !== '');

                // Handle estimated_price → price mapping
                if (! empty($allAttrs['estimated_price']) && empty($allAttrs['price'])) {
                    $allAttrs['price'] = $allAttrs['estimated_price'];
                }
                unset($allAttrs['estimated_price']);

                // Handle estimated cost (~60% of price)
                if (! empty($allAttrs['price']) && is_numeric($allAttrs['price']) && empty($allAttrs['cost'])) {
                    $allAttrs['cost'] = round((float) $allAttrs['price'] * 0.6, 2);
                }

                // Auto-set product_number from SKU if not provided
                if (empty($allAttrs['product_number'])) {
                    $allAttrs['product_number'] = $sku;
                }

                // Remove non-attribute keys
                $categoryValues = null;
                if (! empty($allAttrs['categories'])) {
                    $categoryValues = $allAttrs['categories'];
                }
                unset($allAttrs['categories'], $allAttrs['detected_name'], $allAttrs['product_type']);

                // Load family attributes for dynamic routing
                $familyAttributes = $this->writerService->getFamilyAttributesPublic($familyId);
                $currencies = DB::table('currencies')->where('status', 1)->pluck('code')->toArray() ?: ['USD'];

                $values = $product->values ?? [];
                $values['common']['sku'] = $sku;
                $values['common']['url_key'] = Str::slug($name);

                $skippedAttrs = [];

                foreach ($allAttrs as $code => $value) {
                    if (\in_array($code, ['sku', 'url_key', 'image'], true)) {
                        continue;
                    }

                    if (! isset($familyAttributes[$code])) {
                        $skippedAttrs[] = $code;

                        continue;
                    }

                    $meta = $familyAttributes[$code];

                    // Handle price type → multi-currency object
                    if ($meta['type'] === 'price' && is_numeric($value)) {
                        $priceObj = [];
                        foreach ($currencies as $curr) {
                            $priceObj[$curr] = (string) round((float) $value, 2);
                        }
                        $value = $priceObj;
                    }

                    // Handle select/multiselect → resolve to option code
                    if (\in_array($meta['type'], ['select', 'multiselect'], true) && is_string($value)) {
                        $resolved = $this->writerService->resolveSelectValuePublic($code, $value, $meta['attribute_id']);
                        if ($resolved === null) {
                            $skippedAttrs[] = "{$code} (no matching option for '{$value}')";

                            continue;
                        }
                        $value = $resolved;
                    }

                    // Route to correct bucket
                    if ($meta['value_per_channel'] && $meta['value_per_locale']) {
                        $values['channel_locale_specific'][$context->channel][$context->locale][$code] = $value;
                    } elseif ($meta['value_per_channel']) {
                        $values['channel_specific'][$context->channel][$code] = $value;
                    } elseif ($meta['value_per_locale']) {
                        $values['locale_specific'][$context->locale][$code] = $value;
                    } else {
                        $values['common'][$code] = $value;
                    }
                }

                // Assign categories
                if ($categories || (is_array($categoryValues) && ! empty($categoryValues))) {
                    $catInputs = [];

                    if ($categories) {
                        $catInputs = array_map('trim', explode(',', $categories));
                    }

                    if (is_array($categoryValues)) {
                        $catInputs = array_merge($catInputs, $categoryValues);
                    }

                    // Build candidate codes to search for (exact + slugged last segments)
                    $candidates = [];

                    foreach ($catInputs as $input) {
                        if (! is_string($input)) {
                            continue;
                        }
                        $candidates[] = $input;
                        $segments = array_map('trim', explode('>', $input));
                        $last = end($segments);
                        $candidates[] = $last;
                        $candidates[] = Str::slug($last);
                    }

                    $candidates = array_unique(array_filter($candidates));

                    // Query only matching categories instead of loading all
                    $matched = DB::table('categories')
                        ->whereIn('code', $candidates)
                        ->pluck('code')
                        ->toArray();

                    if (! empty($matched)) {
                        $values['categories'] = array_values(array_unique($matched));
                    }
                }

                // Attach uploaded image
                if ($attach_image && $context->hasImages()) {
                    $imagePath = $context->firstImagePath();

                    if ($imagePath && file_exists($imagePath)) {
                        try {
                            $fileStorer = app(FileStorer::class);
                            $storagePath = 'product'.DIRECTORY_SEPARATOR.$product->id.DIRECTORY_SEPARATOR.'image';

                            $storedImage = $fileStorer->store(
                                $storagePath,
                                new File($imagePath),
                                [FileStorer::HASHED_FOLDER_NAME_KEY => true],
                            );

                            if ($storedImage) {
                                $values['common']['image'] = $storedImage;
                            }
                        } catch (\Throwable) {
                            // Image attachment failed silently
                        }
                    }
                }

                $product->values = $values;
                $product->save();

                $productUrl = route('admin.catalog.products.edit', $product->id);
                $filledAttrs = array_keys(array_merge(
                    $values['common'] ?? [],
                    $values['channel_locale_specific'][$context->channel][$context->locale] ?? [],
                    $values['channel_specific'][$context->channel] ?? [],
                ));

                return json_encode([
                    'product_id'  => $product->id,
                    'sku'         => $sku,
                    'product_url' => $productUrl,
                    'result'      => [
                        'created'    => true,
                        'sku'        => $sku,
                        'filled'     => $filledAttrs,
                        'categories' => $values['categories'] ?? [],
                        'has_image'  => ! empty($values['common']['image']),
                        'skipped'    => empty($skippedAttrs) ? null : $skippedAttrs,
                    ],
                ]);
            });
    }
}
