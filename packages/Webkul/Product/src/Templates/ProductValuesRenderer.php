<?php

declare(strict_types=1);

namespace Webkul\Product\Templates;

use Webkul\Attribute\Services\AttributeService;
use Webkul\Core\Models\Channel;
use Webkul\Product\Contracts\ProductValuesRenderer as ProductValuesRendererContract;
use Webkul\Product\Contracts\TemplateRenderer;
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
     * Render the template sources of every scope in the channel and locale that scope resolves to.
     */
    public function renderAllScopes(array $values): array
    {
        $defaultChannel = $this->defaultChannel();

        $slices = [[[AbstractType::COMMON_VALUES_KEY], $defaultChannel, $this->channelLocale($defaultChannel)]];

        foreach (array_keys($this->slice($values, [AbstractType::LOCALE_VALUES_KEY])) as $locale) {
            $slices[] = [[AbstractType::LOCALE_VALUES_KEY, (string) $locale], $defaultChannel, (string) $locale];
        }

        foreach (array_keys($this->slice($values, [AbstractType::CHANNEL_VALUES_KEY])) as $channel) {
            $slices[] = [[AbstractType::CHANNEL_VALUES_KEY, (string) $channel], (string) $channel, $this->channelLocale((string) $channel)];
        }

        foreach ($this->slice($values, [AbstractType::CHANNEL_LOCALE_VALUES_KEY]) as $channel => $locales) {
            foreach (array_keys(is_array($locales) ? $locales : []) as $locale) {
                $slices[] = [[AbstractType::CHANNEL_LOCALE_VALUES_KEY, (string) $channel, (string) $locale], (string) $channel, (string) $locale];
            }
        }

        return $this->renderSlices($values, $slices);
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
