<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Support\Facades\DB;
use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;
use Webkul\AiAgent\Services\ProductWriterService;

class BulkEdit implements PimTool
{
    public function __construct(
        protected ProductWriterService $writerService,
    ) {}

    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('bulk_edit')
            ->for('Bulk update products matching a rule/filter. Update by status, category, family, or all.')
            ->withEnumParameter('filter_by', 'Filter products by', ['status', 'category', 'family', 'all'])
            ->withStringParameter('filter_value', 'Filter value (e.g. "active", category code, family code)')
            ->withStringParameter('changes_json', 'JSON of attribute changes to apply (e.g. {"status":"inactive","brand":"Nike"})')
            ->withNumberParameter('limit', 'Max products to update (default 50, max 500)')
            ->using(function (
                string $filter_by = 'all',
                ?string $filter_value = null,
                ?string $changes_json = null,
                int $limit = 50,
            ) use ($context): string {
                $changes = $changes_json ? json_decode($changes_json, true) : null;
                if (empty($changes)) {
                    return json_encode(['error' => 'changes_json is required']);
                }

                $limit = min(max($limit, 1), 500);

                $qb = DB::table('products')->select('id', 'sku', 'attribute_family_id', 'values', 'status');

                if ($filter_by === 'status' && $filter_value !== null) {
                    $qb->where('status', $filter_value === 'active' ? 1 : 0);
                } elseif ($filter_by === 'category' && $filter_value) {
                    $qb->whereRaw("JSON_CONTAINS(JSON_EXTRACT(`values`, '$.categories'), ?)", ['"'.$filter_value.'"']);
                } elseif ($filter_by === 'family' && $filter_value) {
                    $familyId = DB::table('attribute_families')->where('code', $filter_value)->value('id');
                    if (! $familyId) {
                        return json_encode(['error' => "Family '{$filter_value}' not found"]);
                    }
                    $qb->where('attribute_family_id', $familyId);
                }

                $products = $qb->limit($limit)->get();

                if ($products->isEmpty()) {
                    return json_encode(['error' => 'No products match the filter']);
                }

                $updated = 0;
                $errors = [];
                $repo = app('Webkul\Product\Repositories\ProductRepository');
                $currencies = DB::table('currencies')->where('status', 1)->pluck('code')->toArray() ?: ['USD'];

                foreach ($products as $p) {
                    try {
                        $values = json_decode($p->values, true) ?? [];
                        $familyAttrs = $this->writerService->getFamilyAttributesPublic($p->attribute_family_id);
                        $statusChanged = false;

                        foreach ($changes as $code => $value) {
                            if ($code === 'status') {
                                DB::table('products')->where('id', $p->id)->update(['status' => (bool) $value]);
                                $statusChanged = true;

                                continue;
                            }

                            if (! isset($familyAttrs[$code])) {
                                continue;
                            }

                            $meta = $familyAttrs[$code];

                            if ($meta['type'] === 'price' && is_numeric($value)) {
                                $priceObj = [];
                                foreach ($currencies as $c) {
                                    $priceObj[$c] = (string) round((float) $value, 2);
                                }
                                $value = $priceObj;
                            }

                            if (\in_array($meta['type'], ['select', 'multiselect']) && is_string($value)) {
                                $resolved = $this->writerService->resolveSelectValuePublic($code, $value, $meta['attribute_id']);
                                if ($resolved === null) {
                                    continue;
                                }
                                $value = $resolved;
                            }

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

                        $repo->updateWithValues(['values' => $values], $p->id);
                        $updated++;
                    } catch (\Throwable $e) {
                        $errors[] = "SKU {$p->sku}: {$e->getMessage()}";
                    }
                }

                return json_encode([
                    'result' => [
                        'matched' => $products->count(),
                        'updated' => $updated,
                        'filter'  => "{$filter_by}={$filter_value}",
                        'errors'  => empty($errors) ? null : array_slice($errors, 0, 5),
                    ],
                ]);
            });
    }
}
