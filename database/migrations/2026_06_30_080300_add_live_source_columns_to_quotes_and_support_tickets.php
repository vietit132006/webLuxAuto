<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table): void {
                if (!Schema::hasColumn('quotes', 'live_session_id')) {
                    $table->foreignId('live_session_id')
                        ->nullable()
                        ->constrained('live_sessions')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('quotes', 'live_lead_id')) {
                    $table->foreignId('live_lead_id')
                        ->nullable()
                        ->constrained('live_leads')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('quotes', 'quotes_live_source_index')) {
                    $table->index(['live_session_id', 'live_lead_id'], 'quotes_live_source_index');
                }
            });
        }

        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table): void {
                if (!Schema::hasColumn('support_tickets', 'live_session_id')) {
                    $table->foreignId('live_session_id')
                        ->nullable()
                        ->constrained('live_sessions')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('support_tickets', 'live_lead_id')) {
                    $table->foreignId('live_lead_id')
                        ->nullable()
                        ->constrained('live_leads')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('support_tickets', 'support_tickets_live_source_index')) {
                    $table->index(['live_session_id', 'live_lead_id'], 'support_tickets_live_source_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table): void {
                if (Schema::hasColumn('support_tickets', 'live_session_id')) {
                    $table->dropForeign(['live_session_id']);
                }

                if (Schema::hasColumn('support_tickets', 'live_lead_id')) {
                    $table->dropForeign(['live_lead_id']);
                }

                $drop = collect(['live_session_id', 'live_lead_id'])
                    ->filter(fn (string $column): bool => Schema::hasColumn('support_tickets', $column))
                    ->all();

                if ($drop !== []) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table): void {
                if (Schema::hasColumn('quotes', 'live_session_id')) {
                    $table->dropForeign(['live_session_id']);
                }

                if (Schema::hasColumn('quotes', 'live_lead_id')) {
                    $table->dropForeign(['live_lead_id']);
                }

                $drop = collect(['live_session_id', 'live_lead_id'])
                    ->filter(fn (string $column): bool => Schema::hasColumn('quotes', $column))
                    ->all();

                if ($drop !== []) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};
