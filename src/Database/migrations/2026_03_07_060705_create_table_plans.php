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
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable(true);
            $table->auditable();
            $table->timestamps();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_id')->nullable(true);

            $table->foreign('pricing_id')
                ->references('id')->on('pricings')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign('branches_pricing_id_foreign');
            $table->dropColumn('pricing_id');
        });

        Schema::dropIfExists('pricings');
    }
};
