<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Support\Facades\DB;
use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;
use Webkul\AiAgent\Services\SemanticRankingService;

class SearchProducts implements PimTool
{
    public function __construct(
        protected SemanticRankingService $semanticRankingService,
    ) {}

    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('search_products')
            ->for('Search products by SKU, name, or status.')
            ->withStringParameter('query', 'Search term: SKU pattern, product name keyword, or leave empty for all')
            ->withEnumParameter('status', 'Filter by product status', ['active', 'inactive', 'all'])
            ->withNumberParameter('limit', 'Maximum results to return (default 10, max 50)')
            ->using(function (?string $query = null, string $status = 'all', int $limit = 10): string {
                $limit = min(max($limit, 1), 50);
                $candidateLimit = min(max($limit * 5, $limit), 200);

                $qb = DB::table('products as p')
                    ->leftJoin('attribute_families as af', 'af.id', '=', 'p.attribute_family_id')
                    ->select(
                        'p.id', 'p.sku', 'p.type', 'p.status', 'af.code as family_code',
                        DB::raw("JSON_UNQUOTE(JSON_EXTRACT(p.values, '$.channel_locale_specific.default.en_US.name')) as product_name"),
                        DB::raw("JSON_UNQUOTE(JSON_EXTRACT(p.values, '$.common.url_key')) as url_key"),
                    );

                if ($query) {
                    $qb->where(function ($q) use ($query) {
                        $q->where('p.sku', 'like', "%{$query}%")
                            ->orWhere('p.values->common->url_key', 'like', "%{$query}%")
                            ->orWhereRaw("JSON_EXTRACT(p.values, '$.channel_locale_specific.default.en_US.name') LIKE ?", ["%{$query}%"]);
                    });
                }

                if ($status !== 'all') {
                    $qb->where('p.status', $status === 'active' ? 1 : 0);
                }

                $products = $qb->orderByDesc('p.id')->limit($candidateLimit)->get();

                $results = $products->map(function ($p) {
                    return [
                        'id'              => $p->id,
                        'sku'             => $p->sku,
                        'name'            => $p->product_name ?? $p->url_key ?? '(unnamed)',
                        'type'            => $p->type,
                        'status'          => $p->status ? 'active' : 'inactive',
                        'family'          => $p->family_code,
                        'relevance_score' => null,
                    ];
                });

                $hasSemanticQuery = ! empty($query) && mb_strlen(trim($query)) > 2;

                if ($hasSemanticQuery && $results->count() > 2) {
                    $documents = $results
                        ->map(fn ($item) => implode(' | ', [
                            $item['sku'],
                            $item['name'],
                            $item['type'],
                            (string) $item['family'],
                            $item['status'],
                        ]))
                        ->values()
                        ->toArray();

                    $ranked = $this->semanticRankingService->rank($query, $documents, $limit);

                    if (! empty($ranked)) {
                        $reranked = collect();

                        foreach ($ranked as $item) {
                            $index = $item['index'];

                            if (! isset($results[$index])) {
                                continue;
                            }

                            $row = $results[$index];
                            $row['relevance_score'] = $item['score'];
                            $reranked->push($row);
                        }

                        $results = $reranked->take($limit)->values();
                    } else {
                        $results = $results->take($limit)->values();
                    }
                } else {
                    $results = $results->take($limit)->values();
                }

                return json_encode([
                    'total'    => $results->count(),
                    'products' => $results->toArray(),
                ]);
            });
    }
}
