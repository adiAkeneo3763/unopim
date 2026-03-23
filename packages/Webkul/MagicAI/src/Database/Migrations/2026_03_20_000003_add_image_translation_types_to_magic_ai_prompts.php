<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to include image and translation types
        $table = DB::getTablePrefix().'magic_ai_prompts';
        DB::statement("ALTER TABLE {$table} MODIFY COLUMN type ENUM('product', 'category', 'image', 'translation') NOT NULL DEFAULT 'product'");
    }

    public function down(): void
    {
        $table = DB::getTablePrefix().'magic_ai_prompts';
        DB::statement("ALTER TABLE {$table} MODIFY COLUMN type ENUM('product', 'category') NOT NULL DEFAULT 'product'");
    }
};
