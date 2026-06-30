<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table): void {
            if (!Schema::hasColumn('live_sessions', 'live_code')) {
                $table->string('live_code', 20)->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('live_sessions', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }

            if (!Schema::hasColumn('live_sessions', 'platform')) {
                $table->string('platform', 40)->default('youtube')->after('description');
            }

            if (!Schema::hasColumn('live_sessions', 'video_url')) {
                $table->string('video_url')->nullable()->after('video_id');
            }

            if (!Schema::hasColumn('live_sessions', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('video_url');
            }

            if (!Schema::hasColumn('live_sessions', 'status')) {
                $table->string('status', 30)->default('draft')->index()->after('thumbnail');
            }

            if (!Schema::hasColumn('live_sessions', 'starts_at')) {
                $table->dateTime('starts_at')->nullable()->index()->after('status');
            }

            if (!Schema::hasColumn('live_sessions', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->index()->after('starts_at');
            }

            if (!Schema::hasColumn('live_sessions', 'host_user_id')) {
                $table->foreignId('host_user_id')
                    ->nullable()
                    ->after('ends_at')
                    ->constrained('users', 'user_id')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('live_sessions', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('is_active');
            }

            if (!Schema::hasColumn('live_sessions', 'replay_enabled')) {
                $table->boolean('replay_enabled')->default(false)->after('is_public');
            }

            if (!Schema::hasColumn('live_sessions', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('replay_enabled');
            }

            if (!Schema::hasColumn('live_sessions', 'peak_viewers')) {
                $table->unsignedInteger('peak_viewers')->default(0)->after('views_count');
            }

            if (!Schema::hasColumn('live_sessions', 'cta_label')) {
                $table->string('cta_label')->nullable()->after('peak_viewers');
            }

            if (!Schema::hasColumn('live_sessions', 'cta_url')) {
                $table->string('cta_url')->nullable()->after('cta_label');
            }

            if (!Schema::hasColumn('live_sessions', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('cta_url')
                    ->constrained('users', 'user_id')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('live_sessions', 'live_sessions_status_starts_index')) {
                $table->index(['status', 'starts_at'], 'live_sessions_status_starts_index');
            }
        });

        DB::table('live_sessions')
            ->whereNull('status')
            ->update(['status' => 'draft']);

        DB::table('live_sessions')
            ->whereNull('platform')
            ->update(['platform' => 'youtube']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('live_sessions')) {
            return;
        }

        Schema::table('live_sessions', function (Blueprint $table): void {
            if (Schema::hasColumn('live_sessions', 'host_user_id')) {
                $table->dropForeign(['host_user_id']);
            }

            if (Schema::hasColumn('live_sessions', 'created_by')) {
                $table->dropForeign(['created_by']);
            }

            $drop = collect([
                'live_code',
                'slug',
                'platform',
                'video_url',
                'thumbnail',
                'status',
                'starts_at',
                'ends_at',
                'host_user_id',
                'is_public',
                'replay_enabled',
                'views_count',
                'peak_viewers',
                'cta_label',
                'cta_url',
                'created_by',
            ])->filter(fn (string $column): bool => Schema::hasColumn('live_sessions', $column))->all();

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
