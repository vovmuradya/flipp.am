<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        // Для SQLite делаем пересоздание таблицы с нужными nullable-столбцами
        if ($driver === 'sqlite') {
            if (!Schema::hasTable('vehicle_details')) {
                return; // нечего менять
            }

            Schema::create('vehicle_details_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('listing_id')
                    ->unique()
                    ->constrained('listings')
                    ->cascadeOnDelete();
                // Делаем эти поля nullable
                $table->string('make', 100)->nullable();
                $table->string('model', 100)->nullable();
                $table->year('year')->nullable();
                $table->unsignedInteger('mileage')->nullable();
                // Остальные как были
                $table->string('body_type', 50)->nullable();
                $table->enum('transmission', ['automatic', 'manual', 'semi-automatic', 'cvt'])->nullable();
                $table->enum('fuel_type', ['gasoline', 'diesel', 'hybrid', 'electric', 'lpg'])->nullable();
                $table->unsignedInteger('engine_displacement_cc')->nullable();
                $table->string('exterior_color', 50)->nullable();
                $table->boolean('is_from_auction')->default(false);
                $table->string('source_auction_url', 512)->nullable();
                $table->timestamps();
                $table->index('make');
                $table->index('model');
                $table->index('year');
                $table->index('is_from_auction');
            });

            try {
                // Переносим данные
                DB::statement('INSERT INTO vehicle_details_tmp (id, listing_id, make, model, year, mileage, body_type, transmission, fuel_type, engine_displacement_cc, exterior_color, is_from_auction, source_auction_url, created_at, updated_at)
                                SELECT id, listing_id, make, model, year, mileage, body_type, transmission, fuel_type, engine_displacement_cc, exterior_color, is_from_auction, source_auction_url, created_at, updated_at
                                FROM vehicle_details');
            } catch (\Throwable $e) {
                Log::warning('Failed to migrate data into vehicle_details_tmp: ' . $e->getMessage());
            }

            Schema::drop('vehicle_details');
            Schema::rename('vehicle_details_tmp', 'vehicle_details');
            return;
        }

        // Для остальных драйверов пробуем изменить через change()
        Schema::table('vehicle_details', function (Blueprint $table) {
            try {
                $table->string('make', 100)->nullable()->change();
                $table->string('model', 100)->nullable()->change();
                $table->year('year')->nullable()->change();
                $table->unsignedInteger('mileage')->nullable()->change();
            } catch (\Throwable $e) {
                Log::warning('Could not alter vehicle_details columns to nullable via change(): ' . $e->getMessage());
            }
        });
    }

    public function down(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver === 'sqlite') {
            if (!Schema::hasTable('vehicle_details')) {
                return;
            }
            // Пересоздаём с NOT NULL
            Schema::create('vehicle_details_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('listing_id')
                    ->unique()
                    ->constrained('listings')
                    ->cascadeOnDelete();
                $table->string('make', 100); // NOT NULL
                $table->string('model', 100); // NOT NULL
                $table->year('year'); // NOT NULL
                $table->unsignedInteger('mileage'); // NOT NULL
                $table->string('body_type', 50)->nullable();
                $table->enum('transmission', ['automatic', 'manual', 'semi-automatic', 'cvt'])->nullable();
                $table->enum('fuel_type', ['gasoline', 'diesel', 'hybrid', 'electric', 'lpg'])->nullable();
                $table->unsignedInteger('engine_displacement_cc')->nullable();
                $table->string('exterior_color', 50)->nullable();
                $table->boolean('is_from_auction')->default(false);
                $table->string('source_auction_url', 512)->nullable();
                $table->timestamps();
                $table->index('make');
                $table->index('model');
                $table->index('year');
                $table->index('is_from_auction');
            });

            try {
                DB::statement('INSERT INTO vehicle_details_tmp (id, listing_id, make, model, year, mileage, body_type, transmission, fuel_type, engine_displacement_cc, exterior_color, is_from_auction, source_auction_url, created_at, updated_at)
                                SELECT id, listing_id, make, model, year, mileage, body_type, transmission, fuel_type, engine_displacement_cc, exterior_color, is_from_auction, source_auction_url, created_at, updated_at
                                FROM vehicle_details');
            } catch (\Throwable $e) {
                Log::warning('Failed to migrate data back into vehicle_details_tmp: ' . $e->getMessage());
            }

            Schema::drop('vehicle_details');
            Schema::rename('vehicle_details_tmp', 'vehicle_details');
            return;
        }

        Schema::table('vehicle_details', function (Blueprint $table) {
            try {
                $table->string('make', 100)->nullable(false)->change();
                $table->string('model', 100)->nullable(false)->change();
                $table->year('year')->nullable(false)->change();
                $table->unsignedInteger('mileage')->nullable(false)->change();
            } catch (\Throwable $e) {
                Log::warning('Could not revert vehicle_details nullable columns: ' . $e->getMessage());
            }
        });
    }
};
