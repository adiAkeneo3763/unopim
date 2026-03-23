<?php

namespace Webkul\AiAgent\Chat\Tools;

use Illuminate\Support\Facades\DB;
use Prism\Prism\Tool;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\AiAgent\Chat\Contracts\PimTool;

class ListCategories implements PimTool
{
    public function register(ChatContext $context): Tool
    {
        return (new Tool)
            ->as('list_categories')
            ->for('List categories by code or name.')
            ->withStringParameter('search', 'Search term to filter categories by code or name')
            ->withNumberParameter('limit', 'Maximum results (default 20)')
            ->using(function (?string $search = null, int $limit = 20): string {
                $limit = min(max($limit, 1), 100);

                $qb = DB::table('categories')
                    ->select('id', 'code', 'parent_id', 'additional_data');

                if ($search) {
                    $qb->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                            ->orWhereRaw("JSON_EXTRACT(additional_data, '$.locale_specific.en_US.name') LIKE ?", ["%{$search}%"]);
                    });
                }

                $categories = $qb->orderBy('_lft')->limit($limit)->get();

                $results = $categories->map(function ($cat) {
                    $data = json_decode($cat->additional_data, true) ?? [];
                    $name = $data['locale_specific']['en_US']['name'] ?? $cat->code;

                    return [
                        'id'        => $cat->id,
                        'code'      => $cat->code,
                        'name'      => $name,
                        'parent_id' => $cat->parent_id,
                    ];
                });

                return json_encode([
                    'total'      => $results->count(),
                    'categories' => $results->toArray(),
                ]);
            });
    }
}
