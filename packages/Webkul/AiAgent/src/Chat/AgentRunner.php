<?php

namespace Webkul\AiAgent\Chat;

use Illuminate\Support\Facades\DB;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Webkul\MagicAI\Enums\AiProvider;

/**
 * Orchestrates the AI agent loop using Prism's built-in tool calling.
 *
 * Replaces the hardcoded dispatchAction() router. The LLM autonomously
 * decides which tools to call, Prism executes them, feeds results back,
 * and iterates until the LLM produces a final text response.
 */
class AgentRunner
{
    /**
     * Maximum tool-call iterations before forcing a response.
     */
    protected const MAX_STEPS = 5;

    public function __construct(
        protected ToolRegistry $toolRegistry,
    ) {}

    /**
     * Run the agent for a single chat turn.
     *
     * @return array{reply: string, action: string, data: array<string, mixed>, product_url?: string}
     */
    public function run(ChatContext $context): array
    {
        $aiProvider = AiProvider::from($context->platform->provider);
        $prismProvider = $aiProvider->toPrismProvider();

        // Configure Prism credentials dynamically (same pattern as LaravelAiAdapter)
        $this->configureProvider($aiProvider, $context);

        // Build the tools array for this request
        $tools = $this->toolRegistry->build($context);

        // Build messages: history + current user message (with images if uploaded)
        $messages = $this->convertHistory($context->history);

        $imageContent = [];
        foreach ($context->uploadedImagePaths as $imgPath) {
            if (file_exists($imgPath)) {
                $compressed = $this->compressImage($imgPath);
                $imageContent[] = Image::fromLocalPath($compressed);
            }
        }

        $messages[] = new UserMessage($context->message, $imageContent);

        // Build the Prism text generation request with tools
        $request = Prism::text()
            ->using($prismProvider, $context->model, [
                'api_key' => $context->platform->api_key,
            ])
            ->withSystemPrompt($this->buildSystemPrompt($context))
            ->withMessages($messages)
            ->withTools($tools)
            ->withMaxSteps(self::MAX_STEPS)
            ->withMaxTokens(4096)
            ->usingTemperature(0.7)
            ->withClientOptions(['timeout' => 120]);

        $response = $request->asText();

        // Build the response in the same format the chat widget expects
        $result = [
            'reply'  => $response->text ?: 'Operation completed.',
            'action' => 'agent_response',
            'data'   => [
                'steps'       => $response->steps->count(),
                'tokens_used' => ($response->usage->promptTokens ?? 0) + ($response->usage->completionTokens ?? 0),
            ],
        ];

        // Extract product_url or download_url from tool results if present
        $this->extractActionResults($response, $result);

        return $result;
    }

    /**
     * Configure Prism provider credentials from the platform record.
     */
    protected function configureProvider(AiProvider $aiProvider, ChatContext $context): void
    {
        $configKey = $aiProvider->configKey();

        config([
            "prism.providers.{$configKey}.api_key" => $context->platform->api_key,
        ]);

        if ($context->platform->api_url) {
            config([
                "prism.providers.{$configKey}.url" => $context->platform->api_url,
            ]);
        }

        if ($context->platform->extras && is_array($context->platform->extras)) {
            foreach ($context->platform->extras as $key => $value) {
                config(["prism.providers.{$configKey}.{$key}" => $value]);
            }
        }
    }

    /**
     * Build the system prompt for the agent.
     */
    protected function buildSystemPrompt(ChatContext $context): string
    {
        $prompt = <<<'PROMPT'
You are Agenting PIM, a product operations assistant in UnoPim (PIM system). Use tools proactively.

Rules:
- If PRODUCT CONTEXT is given below, use that SKU immediately — never ask to confirm.
- Be decisive. Use reasonable defaults. Confirm only before deleting.
- You can SEE uploaded images. For "create from image": detect ALL attributes (name, color, size, brand, category, price) then call create_product with everything in attributes_json + categories + attach_image=true.
- For create/update: pass ALL attributes in attributes_json/changes_json as JSON. Tool handles pricing and select options automatically.
- For image editing (background removal, enhancement, retouching, color changes, etc.): use edit_image with the uploaded image and a clear instruction. For generating new images from text: use generate_image.
PROMPT;

        if ($context->hasProductContext()) {
            $product = DB::table('products')
                ->where('id', $context->productId)
                ->select('id', 'sku', 'type', 'status')
                ->first();

            if ($product) {
                $prompt .= "\n\nPRODUCT CONTEXT: SKU={$product->sku} type={$product->type} status=".($product->status ? 'active' : 'inactive');
                if ($context->productName) {
                    $prompt .= " name={$context->productName}";
                }
                $prompt .= ' — Target this SKU for all operations.';
            }
        }

        if ($context->hasImages()) {
            $prompt .= "\n\n[Image uploaded — visible to you.]";
        }

        $prompt .= "\nLocale:{$context->locale} Channel:{$context->channel}";

        return $prompt;
    }

    /**
     * Convert the chat widget's history array to Prism Message objects.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, UserMessage|AssistantMessage>
     */
    protected function convertHistory(array $history): array
    {
        $messages = [];
        $recent = array_slice($history, -10);

        foreach ($recent as $turn) {
            if (! isset($turn['role'], $turn['content']) || empty($turn['content'])) {
                continue;
            }

            $content = (string) $turn['content'];

            if ($turn['role'] === 'user') {
                $messages[] = new UserMessage($content);
            } else {
                $messages[] = new AssistantMessage($content);
            }
        }

        return $messages;
    }

    /**
     * Extract product_url or download_url from tool call results and add to response.
     *
     * Tools can return JSON with special keys that the widget uses for action buttons.
     *
     * @param  mixed  $response  Prism response
     * @param  array<string, mixed>  $result  Response array (modified by reference)
     */
    protected function extractActionResults(mixed $response, array &$result): void
    {
        foreach ($response->steps as $step) {
            foreach ($step->toolCalls as $toolCall) {
                // Check if any tool result contains a product_url or download_url
                foreach ($step->toolResults as $toolResult) {
                    $decoded = json_decode($toolResult->result, true);

                    if (is_array($decoded)) {
                        if (! empty($decoded['product_url'])) {
                            $result['product_url'] = $decoded['product_url'];
                        }

                        if (! empty($decoded['download_url'])) {
                            $result['download_url'] = $decoded['download_url'];
                        }

                        if (! empty($decoded['result']) && is_array($decoded['result'])) {
                            $result['result'] = $decoded['result'];
                        }
                    }
                }
            }
        }
    }

    /**
     * Compress an image to max 1024px and JPEG quality 80 to reduce API payload.
     * Returns the path to the compressed file (or original if compression fails/not needed).
     */
    protected function compressImage(string $path, int $maxDim = 1024): string
    {
        try {
            $info = getimagesize($path);

            if (! $info) {
                return $path;
            }

            [$width, $height, $type] = $info;

            // Skip if already small enough
            if ($width <= $maxDim && $height <= $maxDim && filesize($path) < 200_000) {
                return $path;
            }

            $source = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => imagecreatefrompng($path),
                IMAGETYPE_WEBP => imagecreatefromwebp($path),
                IMAGETYPE_GIF  => imagecreatefromgif($path),
                default        => null,
            };

            if (! $source) {
                return $path;
            }

            // Calculate new dimensions
            $ratio = min($maxDim / $width, $maxDim / $height, 1.0);
            $newW = (int) round($width * $ratio);
            $newH = (int) round($height * $ratio);

            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $width, $height);

            $compressedPath = sys_get_temp_dir().'/ai_compressed_'.md5($path).'.jpg';
            imagejpeg($resized, $compressedPath, 80);

            imagedestroy($source);
            imagedestroy($resized);

            return $compressedPath;
        } catch (\Throwable) {
            return $path;
        }
    }
}
