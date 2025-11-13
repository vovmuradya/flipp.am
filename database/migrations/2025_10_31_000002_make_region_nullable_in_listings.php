<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver === 'sqlite') {
            if (!Schema::hasTable('listings')) {
                return;
            }

            // На случай незавершённого предыдущего прогона
            Schema::dropIfExists('listings_tmp');

            // Создаем временную таблицу с region_id nullable и listing_type
            Schema::create('listings_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                // Добавляем listing_type согласно ТЗ v2.1
                $table->enum('listing_type', ['vehicle', 'parts'])->default('vehicle')->comment('Тип объявления');
                $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description');
                $table->decimal('price', 12, 2);
                $table->string('currency', 3)->default('USD');
                $table->enum('status', ['draft', 'active', 'sold', 'expired', 'moderation'])->default('moderation');
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamp('promoted_until')->nullable();
                $table->timestamp('last_bumped_at')->nullable();
                $table->string('language', 2);
                $table->boolean('is_from_auction')->default(false);
                $table->timestamps();
                $table->softDeletes();

                // В SQLite избегаем именованных индексов, а также можем пропустить их создание, чтобы не ловить конфликт имён
                // $table->index('status');
                // $table->index('category_id');
                // $table->index('region_id');
                // $table->index('created_at');
                // $table->index('price');
                // $table->index('promoted_until');
            });

            try {
                $listingTypeColumn = Schema::hasColumn('listings', 'listing_type') ? 'listing_type' : "'vehicle' as listing_type";
                $auctionColumn = Schema::hasColumn('listings', 'is_from_auction') ? 'is_from_auction' : '0 as is_from_auction';

                DB::statement("INSERT INTO listings_tmp (
                        id, user_id, category_id, listing_type, region_id, title, slug, description, price, currency, status, views_count, promoted_until, last_bumped_at, language, is_from_auction, created_at, updated_at, deleted_at
                    )
                    SELECT
                        id, user_id, category_id, {$listingTypeColumn}, region_id, title, slug, description, price, currency, status, views_count, promoted_until, last_bumped_at, language, {$auctionColumn}, created_at, updated_at, deleted_at
                    FROM listings");
            } catch (\Throwable $e) {
                Log::warning('Failed to copy data into listings_tmp: ' . $e->getMessage());
            }

            Schema::drop('listings');
            Schema::rename('listings_tmp', 'listings');
            return;
        }

        // Для других драйверов пробуем изменить столбец на nullable
        try {
            Schema::table('listings', function (Blueprint $table) {
                $table->foreignId('region_id')->nullable()->change();
            });
        } catch (\Throwable $e) {
            Log::warning('Could not alter listings.region_id to nullable via change(): ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver === 'sqlite') {
            if (!Schema::hasTable('listings')) {
                return;
            }

            // На случай незавершённого предыдущего прогона
            Schema::dropIfExists('listings_tmp');

            // Возвращаем region_id к NOT NULL, но сохраняем listing_type в схеме
            Schema::create('listings_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->enum('listing_type', ['vehicle', 'parts'])->default('vehicle');
                $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description');
                $table->decimal('price', 12, 2);
                $table->string('currency', 3)->default('USD');
                $table->enum('status', ['draft', 'active', 'sold', 'expired', 'moderation'])->default('moderation');
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamp('promoted_until')->nullable();
                $table->timestamp('last_bumped_at')->nullable();
                $table->string('language', 2);
                $table->boolean('is_from_auction')->default(false);
                $table->timestamps();
                $table->softDeletes();

                // См. комментарий выше про индексы в SQLite
                // $table->index('status');
                // $table->index('category_id');
                // $table->index('region_id');
                // $table->index('created_at');
                // $table->index('price');
                // $table->index('promoted_until');
            });

            try {
                $listingTypeColumn = Schema::hasColumn('listings', 'listing_type') ? 'listing_type' : "'vehicle' as listing_type";
                $auctionColumn = Schema::hasColumn('listings', 'is_from_auction') ? 'is_from_auction' : '0 as is_from_auction';

                DB::statement("INSERT INTO listings_tmp (
                        id, user_id, category_id, listing_type, region_id, title, slug, description, price, currency, status, views_count, promoted_until, last_bumped_at, language, is_from_auction, created_at, updated_at, deleted_at
                    )
                    SELECT
                        id, user_id, category_id, {$listingTypeColumn}, region_id, title, slug, description, price, currency, status, views_count, promoted_until, last_bumped_at, language, {$auctionColumn}, created_at, updated_at, deleted_at
                    FROM listings");
            } catch (\Throwable $e) {
                Log::warning('Failed to copy data back into listings_tmp: ' . $e->getMessage());
            }

            Schema::drop('listings');
            Schema::rename('listings_tmp', 'listings');
            return;
        }

        try {
            Schema::table('listings', function (Blueprint $table) {
                $table->foreignId('region_id')->nullable(false)->change();
            });
        } catch (\Throwable $e) {
            Log::warning('Could not revert listings.region_id to NOT NULL: ' . $e->getMessage());
        }
    }
};
