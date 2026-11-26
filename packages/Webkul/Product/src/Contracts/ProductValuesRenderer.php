<?php

namespace Webkul\Product\Contracts;

interface ProductValuesRenderer
{
    /**
     * Render the template sources visible in the channel and locale, defaulting to the default channel and its default locale.
     */
    public function renderScope(array $values, ?string $channel = null, ?string $locale = null): array;

    /**
     * Render the template sources of every scope, filling the channel or locale a scope does not carry from the given ones.
     */
    public function renderAllScopes(array $values, ?string $channel = null, ?string $locale = null): array;
}
