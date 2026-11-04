<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('projects')) return;
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'wiring_guide')) $table->text('wiring_guide')->nullable();
            if (!Schema::hasColumn('projects', 'maker_name')) $table->string('maker_name', 120)->nullable();
            if (!Schema::hasColumn('projects', 'maker_instagram_url')) $table->text('maker_instagram_url')->nullable();
            if (!Schema::hasColumn('projects', 'maker_github_url')) $table->text('maker_github_url')->nullable();
            if (!Schema::hasColumn('projects', 'maker_linkedin_url')) $table->text('maker_linkedin_url')->nullable();
        });
    }

    public function down(): void
    {
        // Intentionally retain optional project metadata to prevent accidental data loss.
    }
};
