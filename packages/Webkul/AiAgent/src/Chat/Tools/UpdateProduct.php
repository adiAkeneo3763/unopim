<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Support\Facades\DB;
use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;
use Webkul\AiAgent\Services\ProductWriterService;

class UpdateProduct implements PimTool
{
    public function __construct(
        protected ProductWriterService $writerService,
    ) {}

    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('update_product')
            ->for('Update product attributes by SKU. Pass changes as JSON in changes_json.')
            ->withStringParameter('sku', 'Product SKU to update (can be comma-separated for bulk)')
            ->withStringParameter('changes_json', 'JSON string of attribute_code => new_value pairs to update (e.g. {"name": "New Name", "price": 29.99, "color": "Red"})')
            ->using(function (string $sku, string $changes_json) use ($context): string {
                $changes = json_decode($changes_json, true) ?? [];

                if (empty($changes)) {
                    return json_encode(['error' => 'Invalid or empty changes JSON.']);
                }
                $skus = array_map('trim', explode(',', $sku));
                $updated = 0;
                $errors = [];

                $productRepo = app('Webkul\Product\Repositories\ProductRepository');
                $currencies = DB::table('currencies')->where('status', 1)->pluck('code')->toArray() ?: ['USD'];

                foreach ($skus as $s) {
                    $product = $productRepo->findOneByField('sku', $s);

                    if (! $product) {
                        $errors[] = "SKU not found: {$s}";

                        continue;
                    }

                    $productId = data_get($product, 'id');
                    $familyId = data_get($product, 'attribute_family_id');
                    $familyAttributes = $this->writerService->getFamilyAttributesPublic($familyId);

                    $values = $product->values ?? [];

                    foreach ($changes as $code => $value) {
                        if ($code === 'status') {
                            $product->status = (bool) $value;
                            $product->save();

                            continue;
                        }

                        if (! isset($familyAttributes[$code])) {
                            // Unknown attribute — store in common as fallback
                            $values['common'][$code] = $value;

                            continue;
                        }

                        $meta = $familyAttributes[$code];

                        // Handle price type
                        if ($meta['type'] === 'price' && is_numeric($value)) {
                            $priceObj = [];
                            foreach ($currencies as $curr) {
                                $priceObj[$curr] = (string) round((float) $value, 2);
                            }
                            $value = $priceObj;
                        }

                        // Handle select type
                        if (\in_array($meta['type'], ['select', 'multiselect'], true) && is_string($value)) {
                            $resolved = $this->writerService->resolveSelectValuePublic($code, $value, $meta['attribute_id']);
                            if ($resolved === null) {
                                $errors[] = "No matching option for {$code}='{$value}' on SKU {$s}";

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

                    $productRepo->updateWithValues(['values' => $values], $productId);
                    $updated++;
                }

                return json_encode([
                    'result' => [
                        'updated' => $updated,
                        'skus'    => implode(', ', $skus),
                        'errors'  => empty($errors) ? null : $errors,
                    ],
                ]);
            });
    }
}
