<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix index names that exceed MySQL's 64-character identifier limit
     * when a DB table prefix is configured.
     */
    public function up(): void
    {
        $prefix = DB::getTablePrefix();

        $this->fixUniqueIndex(
            'attribute_option_translations',
            ['attribute_option_id', 'locale'],
            $prefix.'attribute_option_translations_attribute_option_id_locale_unique',
            'attr_opt_translations_opt_id_locale_unique'
        );

        $this->fixUniqueIndex(
            'attribute_group_translations',
            ['attribute_group_id', 'locale'],
            $prefix.'attribute_group_translations_attribute_group_id_locale_unique',
            'attr_grp_translations_grp_id_locale_unique'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = DB::getTablePrefix();

        $this->renameIndexIfExists(
            'attribute_option_translations',
            'attr_opt_translations_opt_id_locale_unique',
            $prefix.'attribute_option_translations_attribute_option_id_locale_unique'
        );

        $this->renameIndexIfExists(
            'attribute_group_translations',
            'attr_grp_translations_grp_id_locale_unique',
            $prefix.'attribute_group_translations_attribute_group_id_locale_unique'
        );
    }

    /**
     * Fix a unique index by renaming it if it exists with the old name,
     * or creating it with the new name if it doesn't exist at all.
     */
    private function fixUniqueIndex(string $table, array $columns, string $oldName, string $newName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existingIndexes = $this->getIndexNames($table);

        if (in_array($newName, $existingIndexes)) {
            return;
        }

        if (in_array($oldName, $existingIndexes)) {
            Schema::table($table, function (Blueprint $table) use ($oldName, $newName) {
                $table->renameIndex($oldName, $newName);
            });

            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $newName) {
            $table->unique($columns, $newName);
        });
    }

    /**
     * Rename an index back to its original name if it exists.
     */
    private function renameIndexIfExists(string $table, string $currentName, string $originalName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existingIndexes = $this->getIndexNames($table);

        if (in_array($currentName, $existingIndexes)) {
            Schema::table($table, function (Blueprint $table) use ($currentName, $originalName) {
                $table->renameIndex($currentName, $originalName);
            });
        }
    }

    /**
     * Get all index names for a table.
     */
    private function getIndexNames(string $table): array
    {
        $prefix = DB::getTablePrefix();
        $indexes = Schema::getIndexes($prefix.$table);

        return array_column($indexes, 'name');
    }
};
