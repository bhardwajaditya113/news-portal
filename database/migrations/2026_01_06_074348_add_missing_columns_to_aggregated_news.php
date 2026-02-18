<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aggregated_news', function (Blueprint $table) {
            if (!Schema::hasColumn('aggregated_news', 'description')) {
                $table->text('description')->nullable()->after('content');
            }
            if (!Schema::hasColumn('aggregated_news', 'image_url')) {
                $table->string('image_url')->nullable()->after('image');
            }
            if (!Schema::hasColumn('aggregated_news', 'fetched_at')) {
                $table->timestamp('fetched_at')->nullable()->after('published_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aggregated_news', function (Blueprint $table) {
            $table->dropColumn(['description', 'image_url', 'fetched_at']);
        });
    }
};
