<?php

namespace Webkul\AiAgent\Chat;

use Webkul\MagicAI\Models\MagicAIPlatform;

/**
 * Immutable request-scoped DTO that carries all context the agent needs.
 *
 * Built once per chat request from the HTTP input. Every tool receives
 * this context so it can access the current product, locale, uploaded
 * files, etc. without coupling to the HTTP layer.
 */
final class ChatContext
{
    /**
     * @param  string  $message  The user's text message
     * @param  array<int, array{role: string, content: string}>  $history  Conversation history
     * @param  int|null  $productId  Product being edited (from page context)
     * @param  string|null  $productSku  SKU of the product being edited
     * @param  string|null  $productName  Name of the product being edited
     * @param  string  $locale  Active locale code (e.g. en_US)
     * @param  string  $channel  Active channel code (e.g. default)
     * @param  MagicAIPlatform  $platform  The AI platform record
     * @param  string  $model  The selected AI model name
     * @param  array<string>  $uploadedImagePaths  Stored paths of uploaded images
     * @param  array<string>  $uploadedFilePaths  Stored paths of uploaded CSV/XLSX files
     * @param  string|null  $currentPage  The URL path the user is on
     */
    public function __construct(
        public readonly string $message,
        public readonly array $history,
        public readonly ?int $productId,
        public readonly ?string $productSku,
        public readonly ?string $productName,
        public readonly string $locale,
        public readonly string $channel,
        public readonly MagicAIPlatform $platform,
        public readonly string $model = '',
        public readonly array $uploadedImagePaths = [],
        public readonly array $uploadedFilePaths = [],
        public readonly ?string $currentPage = null,
    ) {}

    /**
     * Whether the user is currently editing a specific product.
     */
    public function hasProductContext(): bool
    {
        return $this->productId !== null;
    }

    /**
     * Whether images were uploaded with this request.
     */
    public function hasImages(): bool
    {
        return ! empty($this->uploadedImagePaths);
    }

    /**
     * Whether spreadsheet files were uploaded with this request.
     */
    public function hasFiles(): bool
    {
        return ! empty($this->uploadedFilePaths);
    }

    /**
     * Get the first uploaded image path, or null.
     */
    public function firstImagePath(): ?string
    {
        return $this->uploadedImagePaths[0] ?? null;
    }
}
