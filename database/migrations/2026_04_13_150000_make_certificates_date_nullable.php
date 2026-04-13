<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // SQLite does not support altering column nullability; rebuild the table.
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');

            // Create new table with nullable date/url/issuer
            Schema::create('certificates_new', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->string('name');
                $table->dateTime('date')->nullable();
                $table->string('issuer')->nullable();
                $table->string('url')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Copy data across
            DB::statement('INSERT INTO certificates_new (id, name, date, issuer, url, created_at, updated_at, deleted_at) SELECT id, name, date, issuer, url, created_at, updated_at, deleted_at FROM certificates;');

            Schema::drop('certificates');
            DB::statement('ALTER TABLE certificates_new RENAME TO certificates;');
            DB::statement('PRAGMA foreign_keys = ON;');

            return;
        }

        // For other drivers, attempt a safe change (requires doctrine/dbal)
        Schema::table('certificates', function (Blueprint $table) {
            $table->dateTime('date')->nullable()->change();
            $table->string('issuer')->nullable()->change();
            $table->string('url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');

            Schema::create('certificates_old', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->string('name');
                $table->dateTime('date');
                $table->string('issuer');
                $table->string('url');
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement('INSERT INTO certificates_old (id, name, date, issuer, url, created_at, updated_at, deleted_at) SELECT id, name, COALESCE(date, "1970-01-01"), issuer, url, created_at, updated_at, deleted_at FROM certificates;');

            Schema::drop('certificates');
            DB::statement('ALTER TABLE certificates_old RENAME TO certificates;');
            DB::statement('PRAGMA foreign_keys = ON;');

            return;
        }

        Schema::table('certificates', function (Blueprint $table) {
            $table->dateTime('date')->nullable(false)->change();
            $table->string('issuer')->nullable(false)->change();
            $table->string('url')->nullable(false)->change();
        });
    }
};
