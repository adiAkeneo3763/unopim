<?php

namespace Webkul\AiAgent\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\AiAgent\Chat\AgentRunner;
use Webkul\AiAgent\Chat\ChatContext;
use Webkul\MagicAI\Models\MagicAIPlatform;
use Webkul\MagicAI\Repository\MagicAIPlatformRepository;

/**
 * Handles AI chat messages from the global floating widget.
 *
 * Uses the AgentRunner (Prism-based tool calling) to let the AI
 * autonomously decide which PIM operations to perform. The controller
 * is intentionally thin — all intelligence is in the tools.
 */
class ChatController extends Controller
{
    public function __construct(
        protected AgentRunner $agentRunner,
        protected MagicAIPlatformRepository $platformRepository,
    ) {}

    /**
     * Handle a chat message (text and/or images/files).
     */
    public function send(Request $request): JsonResponse
    {
        // Decode history from JSON string when sent via FormData
        if (is_string($request->input('history'))) {
            $request->merge(['history' => json_decode($request->input('history'), true) ?: []]);
        }

        $request->validate([
            'message'     => 'required_without_all:images,files|nullable|string|max:50000',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,webp,gif|max:10240',
            'files'       => 'nullable|array|max:3',
            'files.*'     => 'file|mimes:csv,xlsx,xls|max:20480',
            'platform_id' => 'nullable|integer',
            'model'       => 'nullable|string|max:200',
            'context'     => 'nullable|array',
            'history'     => 'nullable|array',
        ]);

        try {
            // Resolve AI platform
            $platform = $this->resolvePlatform(
                (int) $request->input('platform_id', 0),
            );

            if (! $platform) {
                return new JsonResponse([
                    'reply'  => 'No AI platform configured. Please set up a platform in Magic AI settings.',
                    'action' => 'error',
                ], 422);
            }

            $model = (string) $request->input('model', '')
                ?: ($platform->model_list[0] ?? 'gpt-4o');

            // Store uploaded images
            $imagePaths = [];
            foreach ($request->file('images', []) as $image) {
                $stored = $image->store('ai-agent/images', 'public');
                $imagePaths[] = storage_path('app/public/'.$stored);
            }

            // Store uploaded files
            $filePaths = [];
            foreach ($request->file('files', []) as $file) {
                $stored = $file->store('ai-agent/files', 'public');
                $filePaths[] = storage_path('app/public/'.$stored);
            }

            // Build context
            $context = $request->input('context', []);
            $message = $request->input('message', '');

            // If no message but files/images attached, provide a default instruction
            if (empty($message) && (! empty($imagePaths) || ! empty($filePaths))) {
                $message = 'Process the attached file(s).';
            }

            $chatContext = new ChatContext(
                message: $message,
                history: $request->input('history', []),
                productId: ! empty($context['product_id']) ? (int) $context['product_id'] : null,
                productSku: $context['product_sku'] ?? null,
                productName: $context['product_name'] ?? null,
                locale: app()->getLocale() ?: 'en_US',
                channel: 'default',
                platform: $platform,
                model: $model,
                uploadedImagePaths: $imagePaths,
                uploadedFilePaths: $filePaths,
                currentPage: $context['current_page'] ?? null,
            );

            // Run the agent
            $result = $this->agentRunner->run($chatContext);

            return new JsonResponse($result);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'reply'  => $e->getMessage(),
                'action' => 'error',
            ], 422);
        }
    }

    /**
     * Resolve the AI platform to use.
     */
    protected function resolvePlatform(int $platformId): ?MagicAIPlatform
    {
        if ($platformId > 0) {
            $platform = $this->platformRepository->find($platformId);

            if ($platform && $platform->status) {
                return $platform;
            }
        }

        // Try default platform
        $platform = $this->platformRepository->getDefault();

        if ($platform) {
            return $platform;
        }

        // Fallback: first active platform
        $activeList = $this->platformRepository->getActiveList();

        return $activeList->first();
    }

    /**
     * Return the Magic AI configuration info for the chat widget header.
     */
    public function magicAiConfig(): JsonResponse
    {
        $platform = (string) (core()->getConfigData('general.magic_ai.settings.ai_platform') ?? 'openai');
        $models = (string) (core()->getConfigData('general.magic_ai.settings.api_model') ?? '');
        $enabled = (bool) core()->getConfigData('general.magic_ai.settings.enabled');
        $model = trim(explode(',', $models)[0]);

        return new JsonResponse([
            'enabled'  => $enabled,
            'platform' => $platform,
            'model'    => $model ?: ucfirst($platform),
            'label'    => $model ? $model.' ('.ucfirst($platform).')' : ucfirst($platform),
        ]);
    }
}
