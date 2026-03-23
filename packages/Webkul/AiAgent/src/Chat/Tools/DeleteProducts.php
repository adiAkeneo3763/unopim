<?php

namespace Webkul\AiAgent\Chat\Tools;

use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;

class DeleteProducts implements PimTool
{
    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('delete_products')
            ->for('Delete products by SKU. Confirm with user first.')
            ->withStringParameter('skus', 'Comma-separated list of product SKUs to delete')
            ->withBooleanParameter('confirmed', 'Must be true — indicates the user has confirmed deletion')
            ->using(function (string $skus, bool $confirmed = false): string {
                if (! $confirmed) {
                    return json_encode(['error' => 'Deletion not confirmed. Ask the user to confirm before proceeding.']);
                }

                $skuList = array_map('trim', explode(',', $skus));
                $deleted = 0;
                $errors = [];

                $repo = app('Webkul\Product\Repositories\ProductRepository');

                foreach ($skuList as $sku) {
                    $product = $repo->findOneByField('sku', $sku);

                    if (! $product) {
                        $errors[] = "SKU not found: {$sku}";

                        continue;
                    }

                    $repo->delete($product->id);
                    $deleted++;
                }

                return json_encode([
                    'result' => [
                        'deleted' => $deleted,
                        'skus'    => implode(', ', $skuList),
                        'errors'  => empty($errors) ? null : $errors,
                    ],
                ]);
            });
    }
}
