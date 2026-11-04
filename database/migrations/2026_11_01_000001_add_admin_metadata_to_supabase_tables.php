<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('projects')) Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects','category')) $table->string('category',60)->nullable();
            if (!Schema::hasColumn('projects','team')) $table->string('team',80)->nullable();
            if (!Schema::hasColumn('projects','year')) $table->unsignedSmallInteger('year')->nullable();
            if (!Schema::hasColumn('projects','hardware')) $table->string('hardware',240)->nullable();
            if (!Schema::hasColumn('projects','locker')) $table->string('locker',40)->nullable();
            if (!Schema::hasColumn('projects','image_url')) $table->text('image_url')->nullable();
        });
        if (Schema::hasTable('components')) Schema::table('components', function (Blueprint $table) {
            if (!Schema::hasColumn('components','code')) $table->string('code',40)->nullable()->unique();
            if (!Schema::hasColumn('components','category')) $table->string('category',80)->nullable();
            if (!Schema::hasColumn('components','unit')) $table->string('unit',30)->default('unit');
            if (!Schema::hasColumn('components','location')) $table->string('location',80)->nullable();
            if (!Schema::hasColumn('components','description')) $table->text('description')->nullable();
        });
        if (Schema::hasTable('kits')) Schema::table('kits', function (Blueprint $table) {
            if (!Schema::hasColumn('kits','code')) $table->string('code',40)->nullable()->unique();
            if (!Schema::hasColumn('kits','category')) $table->string('category',80)->nullable();
            if (!Schema::hasColumn('kits','description')) $table->text('description')->nullable();
        });
        if (Schema::hasTable('lockers')) Schema::table('lockers', function (Blueprint $table) {
            if (!Schema::hasColumn('lockers','location')) $table->string('location',80)->nullable();
            if (!Schema::hasColumn('lockers','item')) $table->string('item',160)->nullable();
        });
        if (Schema::hasTable('borrowings')) Schema::table('borrowings', function (Blueprint $table) {
            if (!Schema::hasColumn('borrowings','notes')) $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        // Metadata columns are intentionally retained to avoid accidental data loss.
    }
};
