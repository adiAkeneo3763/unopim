@php
    $platforms = app(\Webkul\MagicAI\Repository\MagicAIPlatformRepository::class)->getActivePlatformOptions();
    $selectedPlatformId = core()->getConfigData('general.magic_ai.settings.ai_platform');
@endphp

<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        @lang('admin::app.configuration.index.general.magic-ai.settings.ai-platform')
    </x-admin::form.control-group.label>

    <select
        id="general_magic_ai_settings_ai_platform"
        name="general[magic_ai][settings][ai_platform]"
        class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:bg-cherry-800 dark:border-cherry-800 dark:hover:border-gray-400"
    >
        <option value="0">@lang('admin::app.configuration.platform.fields.use-default')</option>
        @foreach($platforms as $platform)
            <option value="{{ $platform['id'] }}" {{ $selectedPlatformId == $platform['id'] ? 'selected' : '' }}>
                {{ $platform['label'] }}{{ $platform['is_default'] ? ' *' : '' }}
            </option>
        @endforeach
    </select>

    @if(empty($platforms))
        <p class="mt-1 text-xs text-amber-600">
            @lang('admin::app.configuration.platform.setup.no-platform-hint')
        </p>
    @else
        <p class="mt-1 text-xs text-gray-500">
            @lang('admin::app.configuration.platform.fields.use-default-hint')
        </p>
    @endif
</x-admin::form.control-group>
