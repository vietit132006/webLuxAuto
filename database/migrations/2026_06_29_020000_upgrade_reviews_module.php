<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (!Schema::hasColumn('reviews', 'title')) {
                    $table->string('title', 150)->nullable()->after('car_id');
                }
                if (!Schema::hasColumn('reviews', 'status')) {
                    $table->string('status', 30)->default('pending')->after('comment');
                }
                if (!Schema::hasColumn('reviews', 'verified_type')) {
                    $table->string('verified_type', 30)->nullable()->after('status');
                }
                if (!Schema::hasColumn('reviews', 'order_id')) {
                    $table->unsignedBigInteger('order_id')->nullable()->after('verified_type');
                }
                if (!Schema::hasColumn('reviews', 'ticket_id')) {
                    $table->unsignedBigInteger('ticket_id')->nullable()->after('order_id');
                }
                if (!Schema::hasColumn('reviews', 'service_record_id')) {
                    $table->unsignedBigInteger('service_record_id')->nullable()->after('ticket_id');
                }
                if (!Schema::hasColumn('reviews', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('service_record_id');
                }
                if (!Schema::hasColumn('reviews', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('reviews', 'rejected_by')) {
                    $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('reviews', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }
                if (!Schema::hasColumn('reviews', 'rejected_reason')) {
                    $table->text('rejected_reason')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('reviews', 'reply_content')) {
                    $table->text('reply_content')->nullable()->after('rejected_reason');
                }
                if (!Schema::hasColumn('reviews', 'replied_by')) {
                    $table->unsignedBigInteger('replied_by')->nullable()->after('reply_content');
                }
                if (!Schema::hasColumn('reviews', 'replied_at')) {
                    $table->timestamp('replied_at')->nullable()->after('replied_by');
                }
                if (!Schema::hasColumn('reviews', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('replied_at');
                }
                if (!Schema::hasColumn('reviews', 'helpful_count')) {
                    $table->unsignedInteger('helpful_count')->default(0)->after('is_featured');
                }
                if (!Schema::hasColumn('reviews', 'report_count')) {
                    $table->unsignedInteger('report_count')->default(0)->after('helpful_count');
                }
                if (!Schema::hasColumn('reviews', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
                if (!Schema::hasColumn('reviews', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });

            $this->deleteDuplicateReviews();
            $this->addReviewForeignKeys();
            $this->addReviewIndexes();

            DB::table('reviews')
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
                ]);
        }

        if (!Schema::hasTable('review_images')) {
            Schema::create('review_images', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('review_id');
                $table->string('image_path');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('review_id')
                    ->references('review_id')
                    ->on('reviews')
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('review_reports')) {
            Schema::create('review_reports', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('review_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('reason', 120);
                $table->text('note')->nullable();
                $table->string('status', 30)->default('pending');
                $table->unsignedBigInteger('handled_by')->nullable();
                $table->timestamp('handled_at')->nullable();
                $table->timestamps();

                $table->foreign('review_id')
                    ->references('review_id')
                    ->on('reviews')
                    ->cascadeOnDelete();
                $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
                $table->foreign('handled_by')->references('user_id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('review_votes')) {
            Schema::create('review_votes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('review_id');
                $table->unsignedBigInteger('user_id');
                $table->string('type', 30)->default('helpful');
                $table->timestamps();

                $table->foreign('review_id')
                    ->references('review_id')
                    ->on('reviews')
                    ->cascadeOnDelete();
                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                $table->unique(['review_id', 'user_id'], 'review_votes_review_user_unique');
            });
        }

        if (Schema::hasTable('cars')) {
            Schema::table('cars', function (Blueprint $table): void {
                if (!Schema::hasColumn('cars', 'avg_rating')) {
                    $table->decimal('avg_rating', 3, 2)->nullable()->after('reserved_quantity');
                }
                if (!Schema::hasColumn('cars', 'reviews_count')) {
                    $table->unsignedInteger('reviews_count')->default(0)->after('avg_rating');
                }
            });

            $this->recalculateCarRatings();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('review_images');

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $this->dropForeignIfColumnExists($table, 'reviews', 'order_id');
                $this->dropForeignIfColumnExists($table, 'reviews', 'ticket_id');
                $this->dropForeignIfColumnExists($table, 'reviews', 'service_record_id');
                $this->dropForeignIfColumnExists($table, 'reviews', 'approved_by');
                $this->dropForeignIfColumnExists($table, 'reviews', 'rejected_by');
                $this->dropForeignIfColumnExists($table, 'reviews', 'replied_by');
            });

            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropUnique('reviews_user_car_unique');
                $columns = [
                    'title',
                    'status',
                    'verified_type',
                    'order_id',
                    'ticket_id',
                    'service_record_id',
                    'approved_by',
                    'approved_at',
                    'rejected_by',
                    'rejected_at',
                    'rejected_reason',
                    'reply_content',
                    'replied_by',
                    'replied_at',
                    'is_featured',
                    'helpful_count',
                    'report_count',
                    'updated_at',
                    'deleted_at',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('reviews', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('cars')) {
            Schema::table('cars', function (Blueprint $table): void {
                foreach (['avg_rating', 'reviews_count'] as $column) {
                    if (Schema::hasColumn('cars', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function deleteDuplicateReviews(): void
    {
        DB::table('reviews')
            ->select('user_id', 'car_id', DB::raw('MAX(review_id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'car_id')
            ->having('total', '>', 1)
            ->orderBy('user_id')
            ->chunk(100, function ($duplicates): void {
                foreach ($duplicates as $duplicate) {
                    DB::table('reviews')
                        ->where('user_id', $duplicate->user_id)
                        ->where('car_id', $duplicate->car_id)
                        ->where('review_id', '!=', $duplicate->keep_id)
                        ->delete();
                }
            });
    }

    private function addReviewForeignKeys(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            if (Schema::hasTable('orders')) {
                $table->foreign('order_id')->references('order_id')->on('orders')->nullOnDelete();
            }
            if (Schema::hasTable('support_tickets')) {
                $table->foreign('ticket_id')->references('ticket_id')->on('support_tickets')->nullOnDelete();
            }
            if (Schema::hasTable('service_records')) {
                $table->foreign('service_record_id')->references('id')->on('service_records')->nullOnDelete();
            }

            $table->foreign('approved_by')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('rejected_by')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('replied_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    private function addReviewIndexes(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->unique(['user_id', 'car_id'], 'reviews_user_car_unique');
            $table->index(['car_id', 'status'], 'reviews_car_status_index');
            $table->index(['rating', 'status'], 'reviews_rating_status_index');
            $table->index('verified_type', 'reviews_verified_type_index');
        });
    }

    private function recalculateCarRatings(): void
    {
        DB::table('cars')->update([
            'avg_rating' => null,
            'reviews_count' => 0,
        ]);

        DB::table('reviews')
            ->select('car_id', DB::raw('ROUND(AVG(rating), 2) as avg_rating'), DB::raw('COUNT(*) as reviews_count'))
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->groupBy('car_id')
            ->orderBy('car_id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('cars')
                        ->where('car_id', $row->car_id)
                        ->update([
                            'avg_rating' => $row->avg_rating,
                            'reviews_count' => $row->reviews_count,
                        ]);
                }
            });
    }

    private function dropForeignIfColumnExists(Blueprint $table, string $tableName, string $column): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            $table->dropForeign([$column]);
        }
    }
};
