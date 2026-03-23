@inject('coreConfigRepository', 'Webkul\Core\Repositories\CoreConfigRepository')

@php
    $nameKey = $item['key'] . '.' . $field['name'];
    $name = $coreConfigRepository->getNameField($nameKey);
    $value = core()->getConfigData($nameKey);

    $platformId = core()->getConfigData('general.magic_ai.translation.ai_platform');
    $platform = null;

    if ($platformId) {
        $platform = app(\Webkul\MagicAI\Repository\MagicAIPlatformRepository::class)->find($platformId);
    }

    if (!$platform) {
        $platform = app(\Webkul\MagicAI\Repository\MagicAIPlatformRepository::class)->getDefault();
    }

    $modelOptions = [];
    if ($platform) {
        foreach ($platform->model_list as $model) {
            if ($model) {
                $modelOptions[] = ['id' => $model, 'label' => $model];
            }
        }
    }
@endphp

<v-section-model
    label="@lang($field['title'])"
    name="{{ $name }}"
    :value='@json($value)'
    :initial-options='@json($modelOptions)'
    platform-select-id="general_magic_ai_translation_ai_platform"
>
</v-section-model>

@pushOnce('scripts')
    <script type="text/x-template" id="v-section-model-template">
        <x-admin::form.control-group class="last:!mb-0">
            <x-admin::form.control-group.label>
                @{{ label }}
            </x-admin::form.control-group.label>

            <select
                :id="name"
                :name="name"
                v-model="selectedValue"
                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:bg-cherry-800 dark:border-cherry-800 dark:hover:border-gray-400"
            >
                <option value="0">-- Select Model --</option>
                <option v-for="option in currentOptions" :key="option.id" :value="option.id">
                    @{{ option.label }}
                </option>
            </select>

            <p v-if="loading" class="mt-1 text-xs text-violet-600">Loading models...</p>
            <p v-if="!currentOptions.length && !loading" class="mt-1 text-xs text-gray-400">
                No models available. Select a platform or configure one in Platforms tab.
            </p>
        </x-admin::form.control-group>
    </script>

    <script type="module">
        app.component('v-section-model', {
            template: '#v-section-model-template',
            props: ['label', 'name', 'value', 'initialOptions', 'platformSelectId', 'modelType'],
            data() {
                return {
                    selectedValue: this.value,
                    currentOptions: this.initialOptions || [],
                    loading: false,
                };
            },

            mounted() {
                const platformSelect = document.getElementById(this.platformSelectId);
                if (platformSelect) {
                    platformSelect.addEventListener('change', (e) => {
                        this.loadModelsForPlatform(e.target.value);
                    });
                }
            },

            methods: {
                loadModelsForPlatform(platformId) {
                    this.loading = true;

                    let params = {};
                    if (platformId) {
                        params.platform_id = platformId;
                    }
                    if (this.modelType) {
                        params.type = this.modelType;
                    }

                    this.$axios.get("{{ route('admin.magic_ai.model') }}", { params })
                        .then((response) => {
                            this.loading = false;
                            this.currentOptions = response.data.models || [];
                            this.selectedValue = this.currentOptions[0]?.id || '';
                        })
                        .catch(() => { this.loading = false; this.currentOptions = []; });
                },
            },
        });
    </script>
@endPushOnce
