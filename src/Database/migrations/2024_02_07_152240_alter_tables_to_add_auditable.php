<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use LaravelCommon\System\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groupuser_scopes', function (Blueprint $table) {
            $table->auditable();
        });

        Schema::table('groupusers', function (Blueprint $table) {
            $table->auditable();
        });

        Schema::table('logging_configs', function (Blueprint $table) {
            $table->auditable();
        });

        Schema::table('scopes', function (Blueprint $table) {
            $table->auditable();
        });

        Schema::table('user_scopes', function (Blueprint $table) {
            $table->auditable();
        });

        Schema::table('user_tokens', function (Blueprint $table) {
            $table->auditable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->auditable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
