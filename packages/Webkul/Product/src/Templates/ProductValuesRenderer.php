<?php

declare(strict_types=1);

namespace Webkul\Product\Templates;

use Webkul\Attribute\Services\AttributeService;
use Webkul\Core\Models\Channel;
use Webkul\Product\Contracts\ProductValuesRenderer as ProductValuesRendererContract;
use Webkul\Product\Contracts\TemplateRenderer;
use Webkul\Product\Contracts\VariantValueResolver;
use Webkul\Product\Type\AbstractType;

class ProductValuesRenderer implements ProductValuesRendererContract
{
    private ?array $channelLocales = null;

    /**
     * Create a new product values renderer instance.
     */
    public function __construct(
        protected TemplateRenderer $renderer,
        protected TemplateParser $parser,
        protected AttributeService $attributeService,
        protected VariantValueResolver $variantValueResolver,
    ) {}

    /**
     * Render the template sources visible in the channel and locale, defaulting to the default channel and its default locale.
     */
    public function renderScope(array $values, ?string $channel = null, ?string $locale = null): array
    {
        $channel ??= $this->defaultChannel();

        $locale ??= $this->channelLocale($channel);

        return $this->renderSlices($values, [
            [[AbstractType::COMMON_VALUES_KEY], $channel, $locale],
            [[AbstractType::LOCALE_VALUES_KEY, $locale], $channel, $locale],
            [[AbstractType::CHANNEL_VALUES_KEY, $channel], $channel, $locale],
            [[AbstractType::CHANNEL_LOCALE_VALUES_KEY, $channel, $locale], $channel, $locale],
        ]);
    }

    /**
     * Render the template sources of every scope, filling the channel or locale a scope does not carry from the given ones.
     */
    public function renderAllScopes(array $values, ?string $channel = null, ?string $locale = null): array
    {
        $baseChannel = $channel ?? $this->defaultChannel();

        $slices = [[[AbstractType::COMMON_VALUES_KEY], $baseChannel, $locale ?? $this->channelLocale($baseChannel)]];

        foreach (array_keys($this->slice($values, [AbstractType::LOCALE_VALUES_KEY])) as $scopeLocale) {
            $slices[] = [[AbstractType::LOCALE_VALUES_KEY, (string) $scopeLocale], $baseChannel, (string) $scopeLocale];
        }

        foreach (array_keys($this->slice($values, [AbstractType::CHANNEL_VALUES_KEY])) as $scopeChannel) {
            $slices[] = [[AbstractType::CHANNEL_VALUES_KEY, (string) $scopeChannel], (string) $scopeChannel, $locale ?? $this->channelLocale((string) $scopeChannel)];
        }

        foreach ($this->slice($values, [AbstractType::CHANNEL_LOCALE_VALUES_KEY]) as $scopeChannel => $scopeLocales) {
            foreach (array_keys(is_array($scopeLocales) ? $scopeLocales : []) as $scopeLocale) {
                $slices[] = [[AbstractType::CHANNEL_LOCALE_VALUES_KEY, (string) $scopeChannel, (string) $scopeLocale], (string) $scopeChannel, (string) $scopeLocale];
            }
        }

        return $this->renderSlices($values, $slices);
    }

    /**
     * Render every scope of each row (id, parent_id, values) with its inherited values, keyed by row id.
     */
    public function renderRows(iterable $rows): array
    {
        $decodedRows = [];

        foreach ($rows as $row) {
            $values = data_get($row, 'values');

            $decodedRows[(int) data_get($row, 'id')] = [
                'id'        => (int) data_get($row, 'id'),
                'parent_id' => data_get($row, 'parent_id'),
                'values'    => is_string($values) ? (json_decode($values, true) ?? []) : (array) $values,
            ];
        }

        $resolvedValues = $this->variantValueResolver->resolveBatch($decodedRows);

        $renderedRows = [];

        foreach ($decodedRows as $id => $row) {
            $renderedRows[$id] = $this->renderAllScopes($resolvedValues[$id] ?? $row['values']);
        }

        return $renderedRows;
    }

    private function renderSlices(array $values, array $slices): array
    {
        $templates = [];

        foreach ($slices as [$path, $channel, $locale]) {
            foreach ($this->slice($values, $path) as $code => $source) {
                if ($this->parser->containsTemplate($source)) {
                    $templates[] = [[...$path, (string) $code], (string) $code, $source, $channel, $locale];
                }
            }
        }

        if ($templates === []) {
            return $values;
        }

        $attributes = $this->attributeService->findByCodes(array_values(array_unique(array_column($templates, 1)))) ?? [];

        $rendered = $values;

        foreach ($templates as [$path, $code, $source, $channel, $locale]) {
            if (isset($attributes[$code])) {
                data_set($rendered, $path, $this->renderer->render($source, $attributes[$code], $values, $channel, $locale)->output);
            }
        }

        return $rendered;
    }

    private function slice(array $values, array $path): array
    {
        $slice = data_get($values, $path);

        return is_array($slice) ? $slice : [];
    }

    private function defaultChannel(): string
    {
        return (string) core()->getDefaultChannelCode();
    }

    private function channelLocale(string $channel): string
    {
        $this->channelLocales ??= core()->getAllChannels()
            ->load('locales')
            ->mapWithKeys(fn (Channel $model): array => [$model->code => core()->getLocaleCodeInChannel($model)])
            ->all();

        return $this->channelLocales[$channel] ?? core()->getDefaultLocaleCodeFromDefaultChannel();
    }
}
